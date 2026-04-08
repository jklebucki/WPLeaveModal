# WPLeaveModal

To repozytorium z wtyczką WordPress **Leave Modal**. Wtyczka pokazuje odwiedzającym **okno z potwierdzeniem** zanim przejdą na inną stronę (zwykle zewnętrzną). Treść okna i adres docelowy ustawiasz w panelu WordPressa — bez programowania.

## Co musisz wiedzieć na start

- Wtyczkę wgrywasz jako **folder** `wp-leave-modal` (ten wewnątrz tego repozytorium), nie całe repozytorium.
- W panelu WordPress: **Wtyczki** → włącz **Leave Modal**, potem **Ustawienia** → **Leave Modal**.

```
WPLeaveModal/
├── README.md              ← ta instrukcja
└── wp-leave-modal/        ← ten folder kopiujesz do WordPressa
```

## Wymagania

WordPress **6.0** lub nowszy, PHP **7.4** lub nowszy. Licencja wtyczki: **GPL-2.0-or-later**.

---

## Instrukcja krok po kroku

### 1. Instalacja

1. Skopiuj folder **`wp-leave-modal`** do katalogu `wp-content/plugins/` na serwerze  
   *albo* spakuj go do ZIP i wgraj w **Wtyczki → Dodaj nową → Wgraj wtyczkę**.
2. Wejdź w **Wtyczki** i kliknij **Włącz** przy **Leave Modal**.

### 2. Konfiguracja okna (w panelu)

Otwórz **Ustawienia → Leave Modal**. Dla każdego okna (możesz mieć kilka) uzupełnij:

| Pole w panelu | Co to znaczy w praktyce |
|---------------|-------------------------|
| **Slug** | Krótka **nazwa wewnętrzna** (np. `partner`, `default`). Małe litery, cyfry, myślnik. Ta sama nazwa pojawi się w shortcode i w kodzie strony — musi się zgadzać z tym, co wkleisz na stronie. |
| **Modal title** | Nagłówek okienka dla odwiedzającego. |
| **Section 1 — message** | Główny tekst informacji (możesz użyć prostego HTML). |
| **Section 2 — label before URL** | Krótki tekst nad adresem, np. „Przekierujemy Cię na:”. |
| **Redirect URL** | Adres strony, na którą ma trafić osoba po kliknięciu **Kontynuuj**. Zostaw puste tylko wtedy, gdy używasz **zwykłego linku** na stronie i adres wpiszesz w samym linku (patrz niżej). |
| **Cancel / Continue** | Napisy na przyciskach **Anuluj** i **Kontynuuj**. |

Przycisk **Dodaj modal** / **Usuń** pozwala dodać kolejne okna lub usunąć zbędne. Zapisujesz zmiany przyciskiem WordPressa u dołu strony ustawień.

### 3. Jak wstawić okno na stronie — trzy proste sposoby

**Sposób A — Shortcode (najłatwiejszy, bez HTML)**  
W treści strony lub wpisu wklej (podstaw swoją **nazwę slug** z ustawień zamiast `partner`):

```text
[leave_modal_button modal="partner" label="Idź na stronę partnera"]
```

To wstawi **przycisk**. Ten sam efekt ma skrót:

```text
[leave_modal_trigger modal="partner" label="Więcej"]
```

**Sposób B — Shortcode jako zwykły link**

```text
[leave_modal_link modal="partner" href="https://example.org" label="Otwórz stronę partnera"]
```

Zamiast `href` możesz użyć `url="https://…"`.

**Sposób C — Własny HTML (dla osób, które edytują kod)**  
Musisz użyć **tej samej nazwy (slug)**, co w **Ustawienia → Leave Modal**:

Przycisk:

```html
<button type="button" data-wp-leave-modal="partner">Tekst przycisku</button>
```

Zwykły link (adres możesz wpisać tutaj; jeśli w panelu **Redirect URL** jest pusty, **Kontynuuj** użyje tego adresu):

```html
<a href="https://example.org" data-wp-leave-modal="partner">Tekst linku</a>
```

**Uwaga:** Kliknięcie z **Ctrl** (Windows) lub **Cmd** (Mac), **Shift**, lub **środkowym przyciskiem myszy** — otwiera link tak jak zwykle w przeglądarce (np. w nowej karcie), **bez** pokazywania okna. Zwykły pojedynczy klik — pokazuje okno.

### 4. Co jeśli okno się nie pokazuje?

**Od wersji 1.1.2:** jeśli w **Ustawienia → Leave Modal** masz zapisany choć jeden modal, skrypt i styl ładują się na zwykłych stronach frontu automatycznie — także wtedy, gdy shortcode jest w **Elementorze / innym builderze** (treść nie siedzi w surowym polu wpisu). Wcześniejsze wersje mogły wtedy w ogóle nie dołączać plików.

Jeśli nadal nic nie widać: sprawdź, czy **slug** w shortcode / `data-wp-leave-modal` jest **identyczny** jak pole **Slug** w ustawieniach (małe litery, bez spacji).

**Dla zaawansowanych:** wymuszenie ładowania: `add_filter( 'wp_leave_modal_enqueue', '__return_true' );` — wyłączenie automatycznego ładowania przy skonfigurowanym modalu: `add_filter( 'wp_leave_modal_enqueue_if_configured', '__return_false' );`

### 5. Aktualizacja ze starej wersji wtyczki (1.0)

Jeśli wcześniej było jedno globalne okno, ustawienia zostały przeniesione do modala o nazwie **`default`**. W shortcode użyj wtedy `modal="default"`.

---

## Ułatwienia dostępu

Okno można zamknąć klawiszem **Escape**; po zamknięciu fokus wraca do przycisku lub linku, który je otworzył.

---

Pełny opis w formacie katalogu WordPress.org: [wp-leave-modal/readme.txt](wp-leave-modal/readme.txt).
