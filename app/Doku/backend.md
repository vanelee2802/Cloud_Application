# mixmatch – Backend-Überblick

## Architektur

```mermaid
flowchart LR
    A[Browser<br/>Vue 3 + Inertia] -->|HTTP-Anfrage| B[routes/web.php]
    B --> C[Middleware<br/>Login + Rolle]
    C -->|erlaubt| D[Controller]
    C -->|nicht erlaubt| X[403 – Kein Zugriff]
    D --> E[Validierung]
    E --> F[Eloquent Models]
    F --> G[(SQLite<br/>database.sqlite)]
    D -->|Inertia-Seite oder JSON| A
```

## Deployment

```mermaid
flowchart LR
    P[Push auf main] --> Q[CI/CD-Pipeline] --> R[Deployment auf Server] --> S[Automatische Migration]
```

## Beispiel: Design speichern

```mermaid
sequenceDiagram
    participant K as Kundin (Editor)
    participant V as Vue (NailEditor)
    participant L as Laravel (Controller)
    participant D as SQLite
    K->>V: Klick auf „Design speichern“
    V->>V: buildNailsPayload()
    V->>L: POST /designs (Name + Nägel)
    L->>L: Validierung
    alt Daten ungültig
        L-->>V: 422 + Fehlermeldung
    else Daten gültig
        L->>D: Design + Nägel speichern
        D-->>L: neue ID
        L-->>V: 200 + ID
        V-->>K: „Design erfolgreich gespeichert“
    end
```

## Rollen und Rechte

| Bereich              | Kunde | Mitarbeiter | Admin            | Inhaberin           |
|----------------------|:-----:|:-----------:|:----------------:|:-------------------:|
| Meine Designs        | ✔     | ✔           | ✔                | ✔                   |
| Meine Rechnungen     | ✔     | ✔           | ✔                | ✔                   |
| Studio-Dashboard     | –     | ✔           | ✔                | –                   |
| Studioverwaltung     | –     | –           | nur eigenes Studio | alle eigenen Studios |
| Studio-Rechnungen    | –     | –           | ✔                | –                   |
| Preise, Bewertungen, Benutzer | – | –     | –                | ✔                   |

Das Menü blendet Links nur aus – die eigentliche Sperre prüft das Backend (Middleware/Controller).