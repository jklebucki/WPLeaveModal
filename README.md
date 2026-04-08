# WPLeaveModal

Repozytorium zawiera wtyczkę WordPress **Leave Modal** — potwierdzenia wyjścia na zewnętrzny adres URL w postaci modala z konfiguracją w kokpicie.

## Struktura katalogów

```
WPLeaveModal/
├── README.md                 # ten plik
└── wp-leave-modal/           # katalog wtyczki (kopiuj do wp-content/plugins/)
    ├── wp-leave-modal.php
    ├── readme.txt            # opis w formacie WordPress.org
    ├── includes/
    ├── assets/
    └── …
```

W WordPressie aktywujesz folder `wp-leave-modal` jako wtyczkę (nie całe repozytorium).

## Wymagania

- WordPress **6.0+**
- PHP **7.4+**

Licencja wtyczki: **GPL-2.0-or-later** (patrz nagłówek w `wp-leave-modal.php`).

---

## Krótki manual wtyczki

### Instalacja

1. Skopiuj katalog `wp-leave-modal` do `wp-content/plugins/` w swojej instalacji WordPress (albo spakuj go do ZIP i wgraj przez **Wtyczki → Dodaj nową → Wgraj wtyczkę**).
2. Aktywuj **Leave Modal** w menu **Wtyczki**.
3. Przejdź do **Ustawienia → Leave Modal** i zapisz modale.

### Co robi wtyczka

Umożliwia zdefiniowanie **wielu modali** (każdy ma unikalny **slug**). Po kliknięciu w wyzwalacz użytkownik widzi dialog z:

- tytułem,
- treścią sekcji 1 (dozwolony podstawowy HTML),
- etykietą i adresem docelowym (sekcja 2),
- przyciskami **Anuluj** (zamyka modal) i **Kontynuuj** (przekierowanie na zapisany URL).

Przycisk **Kontynuuj** jest aktywny, gdy dla modala zapisano poprawny adres **albo** wyzwalacz to link `<a href="https://…">` z poprawnym `http`/`https` — wtedy adres z `href` jest używany jako docelowy (gdy pole **Redirect URL** w kokpicie jest puste; jeśli jest wypełnione, ma pierwszeństwo).

### Konfiguracja w kokpicie (**Ustawienia → Leave Modal**)

- **Slug** — identyfikator używany w HTML i shortcode (np. `partner`, `default`). Małe litery, cyfry, myślniki.
- **Modal title** — nagłówek okna.
- **Section 1 — message** — treść informacji (HTML jest filtrowany przy zapisie).
- **Section 2 — label before URL** — np. „Zostaniesz przekierowany na:”.
- **Redirect URL** — adres docelowy po **Kontynuuj**.
- **Cancel / Continue button label** — etykiety przycisków w stopce.

Możesz dodać wiele wierszy (**Add modal** / **Remove**). Przy zapisie slugi są unikalne (duplikaty dostają sufiks `-2`, `-3`, itd.).

### Wyzwalanie modala na stronie

**1) Atrybut `data` (dowolny przycisk lub link w HTML)**

Slug musi odpowiadać wpisowi w ustawieniach:

```html
<button type="button" data-wp-leave-modal="partner">Przejdź do partnera</button>
```

Zwykły link — ten sam atrybut; zwykły klik otwiera modal, **Ctrl/Cmd/Shift+klik** i **klik środkowym** zostawiają domyślne zachowanie przeglądarki (np. nowa karta):

```html
<a href="https://example.org" data-wp-leave-modal="partner">Zewnętrzny link</a>
```

**2) Shortcode**

Wymagany jest **`modal`** (lub alias **`id`**) ze slugiem:

```text
[leave_modal_button modal="partner" label="Otwórz modal"]
```

Alias o tej samej funkcji:

```text
[leave_modal_trigger modal="partner" label="Więcej"]
```

Opcjonalnie: `class="moja-klasa"` — dodatkowe klasy CSS przycisku.

**3) Shortcode jako link**

```text
[leave_modal_link modal="partner" href="https://example.org" label="Tekst linku"]
```

Alias adresu: `url="https://…"` zamiast `href`. Generuje element `<a>` z `data-wp-leave-modal`.

### Kiedy ładują się skrypty i styl

Zasoby frontowe ładują się, gdy w treści strony jest shortcode, występuje ciąg `data-wp-leave-modal`, albo wtyczka wywołała ładowanie (np. shortcode w widżecie). Motywy mogą wymusić ładowanie przez filtr:

```php
add_filter( 'wp_leave_modal_enqueue', '__return_true' );
```

### Uaktualnienie z wersji 1.0.x

Starsze ustawienia (jeden globalny modal) są automatycznie migrowane do jednego modala ze slugiem **`default`**. Shortcode w nowej wersji wymaga `modal="default"` (lub innego slug ustawionego w panelu).

### Dostępność (a11y)

Modal ma semantykę dialogu, pułapkę fokusu, zamknięcie klawiszem Escape oraz powrót fokusu do elementu, który otworzył okno.

---

Szczegółowy opis przeznaczony na katalog WordPress.org znajduje się w pliku [wp-leave-modal/readme.txt](wp-leave-modal/readme.txt).
