# Sistem za izbor tema diplomskih i master radova

Laravel web aplikacija za upravljanje temama, mentorstvima, izborom rada, komisijama i odbranama na fakultetu. Sistem ima javni katalog tema i odvojene funkcionalnosti za super administratora, profesora i studenta.

## Mogućnosti

- javni pregled diplomskih i master tema sa pretragom, filterima i stranicama;
- bezbedna prijava bez javne registracije;
- obavezna promena inicijalne ili resetovane lozinke;
- upravljanje profesorima i studentima;
- studentski i profesorski profil;
- institucionalne email adrese na domenu `@ftnkm.rs`;
- kreiranje i izmena tema sa privatnim PDF dokumentom;
- bezbedan izbor slobodne teme kroz transakciju i zaključavanje reda;
- tok statusa `Slobodna → Zauzeta → Odbranjena`;
- oslobađanje teme samo od strane mentora ili administratora;
- komisija sa predsednikom i jednim ili više članova;
- kontrolne table i statistika prilagođene korisničkoj ulozi;
- serverska autorizacija kroz middleware i policy klase;
- responzivan interfejs na srpskom jeziku.

## Tehnologije

- Laravel 12 i PHP 8.3;
- Blade, Bootstrap 5, Bootstrap Icons i Vite;
- MariaDB 11.4;
- Nginx;
- Docker i Docker Compose;
- PHPUnit.

## Korisničke uloge

**Super administrator** upravlja svim korisnicima i temama, resetuje lozinke, prati statistiku i može da vodi proces odbrane.

**Profesor** upravlja svojim temama, vidi studente, oslobađa zauzetu temu, definiše komisiju i evidentira odbranu.

**Student** dopunjava profil, pregleda teme odgovarajućeg nivoa, bira jednu slobodnu temu i prati njen status.

## Sistemski zahtevi

Za preporučeni način pokretanja potrebni su samo:

- Git;
- Docker Engine 24 ili noviji;
- Docker Compose v2.

Lokalna instalacija PHP-a, Composer-a, Node-a i baze nije potrebna.

## Instalacija kroz Docker

Klonirajte repository i uđite u direktorijum projekta:

```bash
git clone <adresa-repozitorijuma>
cd <direktorijum-projekta>
```

Kopirajte razvojnu konfiguraciju:

```bash
cp .env.example .env
```

Podignite PHP, Nginx i MariaDB servise:

```bash
docker compose up -d --build
```

Instalirajte PHP zavisnosti i napravite aplikacioni ključ:

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

Kreirajte bazu i demo podatke:

```bash
docker compose exec app php artisan migrate --seed
```

Instalirajte i kompajlirajte frontend:

```bash
docker compose run --rm node npm install
docker compose run --rm node npm run build
```

Aplikacija je zatim dostupna na [http://localhost:8080](http://localhost:8080). Port se može promeniti kroz `APP_PORT` u `.env` fajlu.

Sve navedene komande mogu se izvršiti i odjednom:

```bash
make install
```

## Podešavanje okruženja

Razvojne vrednosti za bazu već postoje u `.env.example`. Po potrebi promenite:

```dotenv
APP_PORT=8080
APP_FACULTY_NAME="Fakultet tehničkih nauka"
APP_EMAIL_DOMAIN="ftnkm.rs"
DB_DATABASE=diplomski_radovi
DB_USERNAME=laravel
DB_PASSWORD=laravel
DB_ROOT_PASSWORD=root
```

`.env` je ignorisan u Git-u i nikada ne treba da se pošalje u repository.

## Demo nalozi

Demo podaci su namenjeni isključivo lokalnom razvojnom okruženju.

| Uloga | Email | Lozinka |
|---|---|---|
| Super administrator | `admin@ftnkm.rs` | `Lozinka123` |
| Profesor | `petar.milic@ftnkm.rs` | `Lozinka123` |
| Student | `ana.nikolic.it-2021-001@ftnkm.rs` | `Lozinka123` |

Seeder pravi ukupno 6 profesora, 10 studenata, 30 diplomskih i 10 master tema.

## Korisne komande

```bash
make up
make down
make migrate
make seed
make test
make logs
```

Odgovarajuće Docker Compose komande mogu se koristiti i direktno.

## Testovi

Kompletan test paket pokreće se naredbom:

```bash
docker compose exec app php artisan test
```

Testovi koriste SQLite bazu u memoriji i ne menjaju razvojne podatke. Pokrivaju autentifikaciju, deaktivirane naloge, promenu inicijalne lozinke, autorizaciju uloga, administraciju korisnika, teme, izbor teme, zabrane izbora, oslobađanje, odbranu, komisiju i PDF validaciju.

## Struktura baze

Glavne tabele su:

- `users` — autentifikacija, uloga i status naloga;
- `student_profiles` — indeks, nivo studija i dozvoljeni profilni podaci;
- `professor_profiles` — zvanje, katedra i oblast interesovanja;
- `topics` — tema, tip, status, mentor, student, PDF i datumi;
- `defense_committee_members` — normalizovani članovi komisije i njihove uloge.

Strani ključevi sprečavaju uklanjanje korisnika koji pripada istorijskom radu. Takve naloge treba deaktivirati umesto brisati.

## Bezbednost i dokumenti

- lozinke se hash-uju Laravel mehanizmom;
- sve izmene koriste CSRF zaštitu i serversku validaciju;
- pristup se proverava middleware-om i policy klasama;
- deaktivirani nalog ne može da se prijavi;
- PDF dokumenti se čuvaju u privatnom Laravel skladištu;
- rezervacija teme koristi transakciju i `lockForUpdate`;
- `.env`, `vendor`, `node_modules`, build fajlovi i privatni upload-i nisu deo Git istorije.

## Git organizacija

Glavna stabilna grana je `main`. Commitovi predstavljaju završene funkcionalne celine i napisani su na srpskom jeziku. Pre slanja promena proverite:

```bash
git diff
git status
git log --oneline
```

## Ponovno kreiranje razvojne baze

Ako želite potpuno svež skup demo podataka:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

Ova komanda briše sve postojeće podatke u razvojnoj bazi.
