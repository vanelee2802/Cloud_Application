<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\NailStudio;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\User;
use App\Notifications\ReceiptIssuedForStudio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Rechnungen (Druck-Export):
 * - Rechnung entsteht nur bei erfolgreicher Zahlung
 * - keine doppelten Rechnungen, fortlaufende Nummern
 * - korrekte Steuerberechnung (19 % und Kleinunternehmer)
 * - Kopie an die Inhaberin
 * - Download und Verlauf nur für Berechtigte
 */
class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $customer;
    private NailStudio $studio;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Keine echten Mails verschicken, nur prüfen
        Notification::fake();

        // Kein Vite-Build für Inertia-Seiten im Test nötig
        $this->withoutVite();

        $this->owner = User::factory()->create([
            'role'  => 'owner',
            'email' => 'inhaberin@example.de',
        ]);

        $this->customer = User::factory()->create([
            'role'  => 'customer',
            'name'  => 'Anna Kundin',
            'email' => 'anna@example.de',
        ]);

        $this->studio = NailStudio::create([
            'name'       => 'Teststudio',
            'address'    => 'Teststraße 1, 38440 Wolfsburg',
            'owner_id'   => $this->owner->id,
            'tax_number' => '23/456/78901',
            'vat_rate'   => 19,
        ]);

        // Falls eure services-Tabelle weitere Pflichtfelder hat,
        // nennt die Fehlermeldung das Feld – hier einfach ergänzen.
        $this->service = Service::forceCreate([
            'nail_studio_id'   => $this->studio->id,
            'name'             => 'Maniküre',
            'price'            => 25,
            'duration_minutes' => 60,
        ]);
    }

    /**
     * Legt einen Termin mit offener Zahlung an.
     * Falls eure appointments-Tabelle weitere Pflichtfelder hat,
     * nennt die Fehlermeldung das Feld – hier einfach ergänzen.
     */
    private function pendingPayment(float $amount = 25, ?User $customer = null): Payment
    {
        $customer ??= $this->customer;

        $appointment = Appointment::forceCreate([
            'user_id'        => $customer->id,
            'nail_studio_id' => $this->studio->id,
            'service_id'     => $this->service->id,
            'date'           => now()->addDays(3)->toDateString(),
            'time'           => '14:00',
            'status'         => 'awaiting_payment',
            'customer_name'  => $customer->name,
            'customer_email' => $customer->email,
        ]);

        return Payment::create([
            'appointment_id' => $appointment->id,
            'amount'         => $amount,
            'status'         => 'pending',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Erstellung
    |--------------------------------------------------------------------------
    */

    public function test_keine_rechnung_solange_nicht_bezahlt(): void
    {
        $this->pendingPayment();

        $this->assertDatabaseCount('receipts', 0);
    }

    public function test_rechnung_entsteht_wenn_zahlung_bezahlt_wird(): void
    {
        $payment = $this->pendingPayment();

        $payment->update(['status' => 'paid']);

        $this->assertDatabaseCount('receipts', 1);
        $this->assertDatabaseHas('receipts', [
            'payment_id'     => $payment->id,
            'customer_email' => 'anna@example.de',
            'studio_name'    => 'Teststudio',
            'tax_number'     => '23/456/78901',
        ]);
    }

    public function test_fehlgeschlagene_zahlung_erzeugt_keine_rechnung(): void
    {
        $payment = $this->pendingPayment();

        $payment->update(['status' => 'failed']);

        $this->assertDatabaseCount('receipts', 0);
    }

    public function test_keine_doppelte_rechnung_bei_erneutem_speichern(): void
    {
        $payment = $this->pendingPayment();

        $payment->update(['status' => 'paid']);

        // Simuliert z. B. einen doppelten Stripe-Webhook
        $payment->update(['stripe_payment_id' => 'pi_test_123']);
        app(\App\Services\ReceiptService::class)->issueFor($payment->fresh());

        $this->assertDatabaseCount('receipts', 1);
    }

    public function test_rechnungsnummern_sind_fortlaufend(): void
    {
        $first  = $this->pendingPayment();
        $second = $this->pendingPayment();

        $first->update(['status' => 'paid']);
        $second->update(['status' => 'paid']);

        $year = now()->year;

        $this->assertSame(
            ["R{$this->studio->id}-{$year}-00001", "R{$this->studio->id}-{$year}-00002"],
            Receipt::orderBy('id')->pluck('number')->all()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Steuerberechnung
    |--------------------------------------------------------------------------
    */

    public function test_mehrwertsteuer_wird_aus_bruttobetrag_herausgerechnet(): void
    {
        $payment = $this->pendingPayment(25);

        $payment->update(['status' => 'paid']);

        $receipt = Receipt::first();

        $this->assertSame(2500, $receipt->gross_cents);
        $this->assertSame(2101, $receipt->net_cents);
        $this->assertSame(399, $receipt->vat_cents);
        $this->assertSame(19, $receipt->vat_rate);

        // Netto + Steuer ergibt immer genau Brutto
        $this->assertSame($receipt->gross_cents, $receipt->net_cents + $receipt->vat_cents);
    }

    public function test_kleinunternehmer_weist_keine_mehrwertsteuer_aus(): void
    {
        $this->studio->update(['is_small_business' => true]);

        $payment = $this->pendingPayment(25);
        $payment->update(['status' => 'paid']);

        $receipt = Receipt::first();

        $this->assertTrue($receipt->is_small_business);
        $this->assertSame(0, $receipt->vat_rate);
        $this->assertSame(0, $receipt->vat_cents);
        $this->assertSame(2500, $receipt->net_cents);
    }

    public function test_rechnung_bleibt_unveraendert_wenn_studiodaten_sich_aendern(): void
    {
        $payment = $this->pendingPayment();
        $payment->update(['status' => 'paid']);

        // Studio ändert nachträglich Namen und Steuernummer
        $this->studio->update(['name' => 'Neuer Name', 'tax_number' => '99/999/99999']);

        $receipt = Receipt::first();

        $this->assertSame('Teststudio', $receipt->studio_name);
        $this->assertSame('23/456/78901', $receipt->tax_number);
    }

    /*
    |--------------------------------------------------------------------------
    | Mail an das Studio
    |--------------------------------------------------------------------------
    */

    public function test_inhaberin_bekommt_rechnungskopie(): void
    {
        $payment = $this->pendingPayment();
        $payment->update(['status' => 'paid']);

        Notification::assertSentOnDemand(
            ReceiptIssuedForStudio::class,
            fn ($notification, $channels, $notifiable) =>
                $notifiable->routes['mail'] === 'inhaberin@example.de'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Download und Verlauf
    |--------------------------------------------------------------------------
    */

    public function test_kundin_kann_eigene_rechnung_herunterladen(): void
    {
        $payment = $this->pendingPayment();
        $payment->update(['status' => 'paid']);

        $receipt = Receipt::first();

        $this->actingAs($this->customer)
            ->get(route('receipts.download', $receipt))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_inhaberin_kann_rechnung_ihres_studios_herunterladen(): void
    {
        $payment = $this->pendingPayment();
        $payment->update(['status' => 'paid']);

        $this->actingAs($this->owner)
            ->get(route('receipts.download', Receipt::first()))
            ->assertOk();
    }

    public function test_fremde_person_bekommt_403(): void
    {
        $payment = $this->pendingPayment();
        $payment->update(['status' => 'paid']);

        $stranger = User::factory()->create(['role' => 'customer']);

        $this->actingAs($stranger)
            ->get(route('receipts.download', Receipt::first()))
            ->assertForbidden();
    }

    public function test_gast_wird_zum_login_geschickt(): void
    {
        $payment = $this->pendingPayment();
        $payment->update(['status' => 'paid']);

        $this->get(route('receipts.download', Receipt::first()))
            ->assertRedirect(route('login'));
    }

    public function test_kundin_sieht_nur_eigene_rechnungen(): void
    {
        $other = User::factory()->create(['role' => 'customer']);

        $this->pendingPayment(25, $this->customer)->update(['status' => 'paid']);
        $this->pendingPayment(25, $other)->update(['status' => 'paid']);

        $this->actingAs($this->customer)
            ->get(route('receipts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Rechnungen/Index')
                ->has('receipts', 1)
                ->where('receipts.0.studio_name', 'Teststudio')
            );
    }

    public function test_kundin_hat_keinen_zugriff_auf_studio_rechnungen(): void
    {
        $this->actingAs($this->customer)
            ->get(route('receipts.studio'))
            ->assertForbidden();
    }

    public function test_inhaberin_sieht_alle_rechnungen_ihres_studios(): void
    {
        $other = User::factory()->create(['role' => 'customer']);

        $this->pendingPayment(25, $this->customer)->update(['status' => 'paid']);
        $this->pendingPayment(25, $other)->update(['status' => 'paid']);

        $this->actingAs($this->owner)
            ->get(route('receipts.studio'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Rechnungen/Index')
                ->where('mode', 'studio')
                ->has('receipts', 2)
            );
    }
}