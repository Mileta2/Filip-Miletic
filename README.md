# Sistem za izbor tema diplomskih i master radova

Web aplikacija Fakulteta tehničkih nauka u Prištini za objavljivanje, izbor i praćenje tema diplomskih i master radova. Sistem objedinjuje administraciju korisnika, mentorski rad, izbor teme, evidenciju komisije i završetak odbrane.

Interfejs, validacione poruke i poslovni pojmovi u aplikaciji napisani su na srpskom jeziku.

## Sadržaj

- [Namena sistema](#namena-sistema)
- [Korisničke uloge i use case-ovi](#korisničke-uloge-i-use-case-ovi)
- [Najvažnija poslovna pravila](#najvažnija-poslovna-pravila)
- [Tehnologije i servisi](#tehnologije-i-servisi)
- [Pokretanje na Ubuntu sistemu od nule](#pokretanje-na-ubuntu-sistemu-od-nule)
- [Demo nalozi](#demo-nalozi)
- [Svakodnevni rad sa projektom](#svakodnevni-rad-sa-projektom)
- [Testiranje](#testiranje)
- [Baza podataka i rezervne kopije](#baza-podataka-i-rezervne-kopije)
- [Struktura projekta](#struktura-projekta)
- [Bezbednost](#bezbednost)
- [Rešavanje čestih problema](#rešavanje-čestih-problema)

## Namena sistema

Sistem rešava ceo proces rada sa završnim temama na jednom mestu:

1. profesor objavljuje temu za određeni predmet i nivo studija;
2. student pregleda samo teme koje odgovaraju njegovom nivou studija;
3. student bira jednu slobodnu temu;
4. izabrana tema prelazi iz statusa **Slobodna** u status **Zauzeta**;
5. mentor može osloboditi temu ako student odustane;
6. mentor definiše komisiju i datum odbrane;
7. nakon odbrane tema dobija status **Odbranjena**.

U terminologiji aplikacije:

- student osnovnih studija bira temu za **diplomski rad**;
- student master studija bira temu za **master rad**.

## Korisničke uloge i use case-ovi

### Javni posetilac

Za pregled javnog kataloga nije potrebna prijava.

Posetilac može da:

- otvori početnu stranicu i pročita namenu sistema;
- pregleda diplomske i master teme;
- filtrira teme po profesoru, predmetu i statusu;
- pretražuje teme po naslovu i drugim dostupnim podacima;
- otvori detaljan prikaz pojedinačne teme.

### Student

Student može da:

- prijavi se nalogom koji je kreirao administrator;
- promeni inicijalnu ili resetovanu lozinku pri prvoj prijavi;
- dopuni dozvoljene podatke profila, kao što su datum rođenja, telefon, mesto i adresa;
- pregleda samo katalog koji odgovara njegovom nivou studija;
- izabere jednu slobodnu temu;
- prati mentora, status teme, datum rezervacije, datum odbrane i sastav komisije;
- otvori stranicu „Moja tema“.

Student ne može sam da menja:

- ime i prezime;
- email adresu;
- broj indeksa;
- nivo i godinu studija.

Te službene podatke održava super administrator.

### Profesor

Profesor može da:

- kreira diplomske i master teme za predmete koje drži;
- pregleda i izmeni svoje teme;
- obriše svoje teme, bez obzira na njihov trenutni status;
- doda ili zameni privatni PDF dokument teme;
- vidi studenta koji je izabrao temu;
- oslobodi zauzetu temu;
- definiše predsednika i članove komisije;
- evidentira datum odbrane;
- menja oblast interesovanja na svom profilu.

Profesor ne može sam da menja ime i prezime, akademsko zvanje i katedru. Te podatke održava super administrator.

### Super administrator

Super administrator ima potpunu kontrolu nad sistemom i može da:

- pregleda statistiku studenata, profesora i tema;
- kreira, menja, aktivira, deaktivira i briše studentske naloge;
- kreira, menja, aktivira, deaktivira i briše profesorske naloge;
- resetuje lozinke korisnika;
- menja godinu i nivo studija;
- upravlja svim temama;
- obriše bilo koju temu;
- vodi proces oslobađanja teme i odbrane;
- pregleda teme povezane sa profesorom pre njegovog brisanja.

Prilikom kreiranja studenta:

- email se automatski formira kao `ime.prezime.broj-godina@ftnkm.rs`;
- administrator po potrebi može da uključi ručnu izmenu email adrese;
- inicijalna lozinka se bezbedno generiše na serveru i prikazuje administratoru nakon uspešnog kreiranja naloga.

## Najvažnija poslovna pravila

- Nema javne registracije. Sve naloge kreira super administrator.
- Svi korisnički emailovi pripadaju domenu `@ftnkm.rs`.
- Broj indeksa čuva se u obliku `broj/godina`, na primer `32009/2025`.
- Student osnovnih studija ne vidi master katalog, a master student ne vidi diplomski katalog.
- Student može imati samo jednu aktivno izabranu temu.
- Samo slobodna tema može biti izabrana.
- Izbor teme obavlja se u transakciji uz zaključavanje reda, čime se sprečava da dva studenta istovremeno izaberu istu temu.
- Profesor može menjati i brisati samo svoje teme.
- Super administrator može menjati i brisati sve teme.
- Deaktivirani korisnik ne može da se prijavi.
- Deaktiviranjem profesora njegove teme ostaju u bazi, ali nisu vidljive u sistemu.
- Brisanjem profesora brišu se i sve teme kojima je bio mentor.
- Brisanje studenta zahteva potvrdu lozinkom trenutno prijavljenog administratora.
- PDF dokumenti tema nisu javno dostupni direktnim URL-om.

## Tehnologije i servisi

| Sloj | Tehnologija |
|---|---|
| Backend | PHP 8.3 u Docker okruženju, Laravel 12 |
| Frontend | Blade, Bootstrap 5, Bootstrap Icons, Vite |
| Baza | MariaDB 11.4 |
| Web server | Nginx 1.27 |
| Frontend alati | Node.js 22 |
| Testovi | PHPUnit 11, SQLite baza u memoriji |
| Razvojno okruženje | Docker Engine i Docker Compose v2 |

Docker Compose pokreće sledeće servise:

- `app` — PHP-FPM i Laravel aplikacija;
- `nginx` — web server dostupan na portu `8080`;
- `db` — MariaDB baza, lokalno izložena na portu `3307`;
- `node` — pomoćni servis za instalaciju i kompajliranje frontend resursa.

## Pokretanje na Ubuntu sistemu od nule

Preporučeni način rada je kroz Docker. Nije potrebno zasebno instalirati PHP, Composer, Node.js, Nginx ili MariaDB jer se sve pokreće u kontejnerima.

### 1. Ažuriranje sistema i instalacija osnovnih alata

Otvorite Terminal i pokrenite:

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y git ca-certificates curl
```

Proverite Git:

```bash
git --version
```

### 2. Instalacija Docker Engine-a i Docker Compose-a

Dodajte zvanični Docker repozitorijum:

```bash
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

sudo tee /etc/apt/sources.list.d/docker.sources > /dev/null <<EOF
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}")
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo systemctl enable --now docker
```

Proverite instalaciju:

```bash
sudo docker run --rm hello-world
sudo docker compose version
```

Komande u nastavku mogu se izvršavati sa `sudo docker ...`. Ako želite da koristite Docker bez `sudo`, dodajte trenutnog korisnika u Docker grupu:

```bash
sudo usermod -aG docker "$USER"
newgrp docker
docker run --rm hello-world
```

> Članstvo u grupi `docker` daje korisniku privilegije slične administratorskim. Na deljenom ili produkcionom računaru procenite da li je prikladnije koristiti `sudo`.

Aktuelno zvanično uputstvo dostupno je na stranici [Install Docker Engine on Ubuntu](https://docs.docker.com/engine/install/ubuntu/).

### 3. Preuzimanje projekta sa GitHuba

Izaberite direktorijum u kojem želite projekat, na primer `~/projekti`:

```bash
mkdir -p ~/projekti
cd ~/projekti
git clone https://github.com/Mileta2/Filip-Miletic.git
cd Filip-Miletic
```

Proverite da li postoje ključni fajlovi:

```bash
ls
```

Treba da vidite najmanje `artisan`, `composer.json`, `package.json`, `compose.yaml` i `.env.example`.

### 4. Kreiranje lokalne konfiguracije

Kopirajte primer konfiguracije:

```bash
cp .env.example .env
```

Podrazumevane razvojne vrednosti već odgovaraju Docker okruženju:

```dotenv
APP_URL=http://localhost:8080
APP_PORT=8080
APP_FACULTY_NAME="Fakultet tehničkih nauka u Prištini"
APP_EMAIL_DOMAIN="ftnkm.rs"

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=diplomski_radovi
DB_USERNAME=laravel
DB_PASSWORD=laravel
DB_ROOT_PASSWORD=root
DB_PORT_FORWARD=3307
```

Za lokalni razvoj ove vrednosti mogu ostati nepromenjene. Za produkciju obavezno koristite nove, jake lozinke.

### 5. Izgradnja i pokretanje kontejnera

```bash
docker compose up -d --build
```

Prvo pokretanje preuzima Docker slike i pravi PHP kontejner, pa može trajati nekoliko minuta.

Proverite stanje servisa:

```bash
docker compose ps
```

Servisi `app`, `nginx` i `db` treba da budu u stanju `Up`, a baza nakon kratkog čekanja treba da bude označena kao `healthy`.

### 6. Instalacija PHP paketa i generisanje aplikacionog ključa

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

`key:generate` upisuje jedinstveni `APP_KEY` u lokalni `.env` fajl.

### 7. Kreiranje baze i demo podataka

```bash
docker compose exec app php artisan migrate --seed
```

Ova komanda:

- pravi sve tabele;
- dodaje super administratora;
- dodaje profesore i studente;
- dodaje diplomske i master teme;
- dodaje primere slobodnih, zauzetih i odbranjenih tema.

### 8. Instalacija i kompajliranje frontend resursa

```bash
docker compose run --rm node npm install
docker compose run --rm node npm run build
```

Ovaj korak je obavezan. Bez njega Laravel može prikazati grešku da Vite manifest ne postoji ili stranica može biti bez stilova.

### 9. Otvaranje aplikacije

U pregledaču otvorite:

```text
http://localhost:8080
```

Ako je port `8080` zauzet, promenite sledeće vrednosti u `.env`:

```dotenv
APP_PORT=8081
APP_URL=http://localhost:8081
```

Zatim ponovo pokrenite kontejnere:

```bash
docker compose down
docker compose up -d
```

## Demo nalozi

Demo podaci su namenjeni isključivo lokalnom razvoju i prezentaciji.

| Uloga | Email | Lozinka |
|---|---|---|
| Super administrator | `admin@ftnkm.rs` | `Lozinka123` |
| Profesor | `petar.milic@ftnkm.rs` | `Lozinka123` |
| Student osnovnih studija | `ana.nikolic.101-22@ftnkm.rs` | `Lozinka123` |

Seeder kreira:

- 1 super administratora;
- 6 profesora;
- 10 studenata;
- 30 diplomskih tema;
- 10 master tema.

Nalozi koje administrator naknadno kreira dobijaju nasumičnu inicijalnu lozinku i zahtev za promenu lozinke pri prvoj prijavi.

## Svakodnevni rad sa projektom

### Pokretanje već instaliranog projekta

```bash
cd ~/projekti/Filip-Miletic
docker compose up -d
```

### Zaustavljanje projekta

```bash
docker compose down
```

Podaci iz baze ostaju sačuvani u Docker volumenu.

### Pregled stanja i logova

```bash
docker compose ps
docker compose logs -f app nginx db
```

Iz pregleda logova izlazi se kombinacijom `Ctrl+C`.

### Preuzimanje novih izmena sa GitHuba

```bash
cd ~/projekti/Filip-Miletic
git pull origin main
docker compose up -d --build
docker compose exec app composer install
docker compose run --rm node npm install
docker compose run --rm node npm run build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
```

`migrate --force` izvršava samo nove migracije i ne briše postojeće podatke.

### Laravel komandna linija

Laravel komande se izvršavaju unutar `app` kontejnera:

```bash
docker compose exec app php artisan about
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:status
docker compose exec app php artisan optimize:clear
```

### Make prečice

Ako instalirate `make` komandom `sudo apt install make`, dostupne su prečice:

```bash
make install
make up
make down
make build
make migrate
make seed
make test
make logs
```

`make install` je prečica za kompletno prvo postavljanje projekta nakon što je napravljen `.env` fajl.

## Testiranje

Kompletan test paket pokreće se naredbom:

```bash
docker compose exec app php artisan test
```

Ili Composer skriptom:

```bash
docker compose exec app composer test
```

Testovi koriste SQLite bazu u memoriji i ne menjaju razvojnu MariaDB bazu.

Formatiranje PHP koda proverava se Laravel Pint alatom:

```bash
docker compose exec app vendor/bin/pint --test
```

Produkcijski frontend build proverava se komandom:

```bash
docker compose run --rm node npm run build
```

## Baza podataka i rezervne kopije

### Glavne tabele

- `users` — prijava, uloga, status naloga i soft delete;
- `student_profiles` — broj indeksa, nivo i godina studija i profilni podaci;
- `professor_profiles` — akademsko zvanje, katedra i oblast interesovanja;
- `topics` — naslov, predmet, opis, tip, status, mentor, student, PDF i datumi;
- `defense_committee_members` — predsednik i članovi komisije;
- `sessions`, `cache`, `jobs` — pomoćne Laravel tabele.

### Pravljenje rezervne kopije baze

Iz korena projekta pokrenite:

```bash
docker compose exec -T db sh -c 'mariadb-dump -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' > backup-$(date +%F-%H%M).sql
```

### Vraćanje rezervne kopije

Sledeća komanda upisuje sadržaj rezervne kopije u razvojnu bazu:

```bash
docker compose exec -T db sh -c 'mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' < backup-YYYY-MM-DD-HHMM.sql
```

Pre vraćanja proverite naziv fajla i po mogućnosti napravite novu rezervnu kopiju trenutne baze.

### Potpuno ponovno kreiranje demo baze

```bash
docker compose exec app php artisan migrate:fresh --seed
```

> **Upozorenje:** `migrate:fresh --seed` trajno briše sve tabele i postojeće podatke, a zatim ponovo unosi demo podatke. Koristite je samo u razvojnom okruženju.

### Potpuno uklanjanje Docker baze

```bash
docker compose down -v
```

> **Upozorenje:** opcija `-v` briše Docker volumen sa MariaDB podacima. Obična komanda `docker compose down` ne briše bazu.

## Struktura projekta

```text
app/
├── Enums/                 statusi, uloge i nivoi studija
├── Http/Controllers/      kontroleri po funkcionalnim celinama
├── Http/Middleware/       provera uloge, statusa i promene lozinke
├── Http/Requests/         validacija ulaznih podataka
├── Models/                Eloquent modeli
├── Policies/              autorizacija rada sa temama
└── Support/               pomoćne klase

database/
├── factories/             podaci za automatske testove
├── migrations/            struktura baze
└── seeders/               demo profesori, studenti i teme

resources/
├── css/                   stilovi aplikacije
├── js/                    JavaScript i Bootstrap
└── views/                 Blade stranice

routes/web.php             sve web rute
tests/                     automatski testovi
docker/                    PHP i Nginx konfiguracija
compose.yaml               definicija Docker servisa
```

## Bezbednost

Sistem koristi sledeće zaštite:

- Laravel hashiranje lozinki;
- automatski generisane inicijalne lozinke;
- obaveznu promenu inicijalne i resetovane lozinke;
- CSRF zaštitu svih obrazaca;
- serversku validaciju podataka;
- middleware provere prijave, aktivnog naloga i korisničke uloge;
- policy autorizaciju nad temama;
- transakciju i `lockForUpdate` pri izboru teme;
- privatno skladištenje PDF dokumenata;
- soft delete korisničkih naloga gde se čuvaju istorijski podaci.

Za produkciono okruženje obavezno:

- postavite `APP_ENV=production`;
- postavite `APP_DEBUG=false`;
- promenite sve demo i lozinke baze;
- nemojte pokretati `db:seed` ili `migrate:fresh` nad produkcionim podacima;
- koristite HTTPS;
- redovno pravite rezervne kopije baze i privatnih dokumenata;
- ne šaljite `.env` fajl u Git repozitorijum.

## Rešavanje čestih problema

### `permission denied while trying to connect to the Docker daemon socket`

Koristite `sudo docker ...` ili dodajte korisnika u Docker grupu:

```bash
sudo usermod -aG docker "$USER"
newgrp docker
```

### Port `8080` je zauzet

Promenite `APP_PORT` i `APP_URL` u `.env`, zatim ponovo pokrenite kontejnere.

### `Vite manifest not found`

Frontend nije kompajliran:

```bash
docker compose run --rm node npm install
docker compose run --rm node npm run build
```

### `vendor/autoload.php` ne postoji

PHP paketi nisu instalirani:

```bash
docker compose exec app composer install
```

### `No application encryption key has been specified`

Generišite Laravel ključ:

```bash
docker compose exec app php artisan key:generate
```

### Aplikacija ne može da se poveže sa bazom

Proverite servise i log baze:

```bash
docker compose ps
docker compose logs db
```

Proverite da u `.env` stoji `DB_HOST=db`, a ne `localhost`.

### Izmene se ne vide

Očistite Laravel keš i ponovo kompajlirajte frontend:

```bash
docker compose exec app php artisan optimize:clear
docker compose run --rm node npm run build
```

### Potrebna je potpuno čista lokalna instalacija

Prvo napravite backup ako su podaci važni. Zatim:

```bash
docker compose down -v
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose run --rm node npm install
docker compose run --rm node npm run build
```

## Git pravila projekta

- Glavna stabilna grana je `main`.
- Commit poruke i komentari u izvornom kodu pišu se na srpskom jeziku.
- `.env`, `vendor`, `node_modules`, frontend build, privatni dokumenti i logovi nisu deo Git istorije.
- Pre slanja izmena proverite:

```bash
git status
git diff
docker compose exec app php artisan test
docker compose run --rm node npm run build
```
