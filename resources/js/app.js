import './bootstrap';
import 'bootstrap';

const normalizeEmailPart = (value) => value
    .trim()
    .replaceAll('Đ', 'Dj')
    .replaceAll('đ', 'dj')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');

const studentEmailGenerator = document.querySelector('[data-student-email-generator]');

if (studentEmailGenerator) {
    const form = studentEmailGenerator.closest('form');
    const firstName = form.querySelector('#first_name');
    const lastName = form.querySelector('#last_name');
    const indexPrefix = form.querySelector('#index_number_prefix');
    const indexSuffix = form.querySelector('#index_number_suffix');
    const email = form.querySelector('#email');
    const editEmail = form.querySelector('[data-email-edit]');
    const automaticEmail = Boolean(
        studentEmailGenerator.dataset.automaticEmail === 'true'
        && firstName
        && lastName
        && indexPrefix
        && indexSuffix,
    );
    let customEmail = !automaticEmail;

    const refreshStudentEmail = () => {
        // Ručno izmenjena adresa se više ne prepisuje automatskim predlogom.
        if (!automaticEmail || customEmail) {
            return;
        }

        const completed = firstName.value.trim() && lastName.value.trim() && indexPrefix.value && indexSuffix.value;
        email.value = completed
            ? `${[
                firstName.value,
                lastName.value,
                `${indexPrefix.value}-${indexSuffix.value}`,
            ].map(normalizeEmailPart).join('.')}@${studentEmailGenerator.dataset.emailDomain}`
            : '';
    };

    [indexPrefix, indexSuffix].forEach((field) => {
        field.addEventListener('input', () => {
            field.value = field.value.replace(/\D/g, '');
            refreshStudentEmail();
        });
        field.addEventListener('change', refreshStudentEmail);
    });

    if (automaticEmail) {
        [firstName, lastName].forEach((field) => {
            field.addEventListener('input', refreshStudentEmail);
            field.addEventListener('change', refreshStudentEmail);
        });
    }

    editEmail.addEventListener('click', () => {
        email.readOnly = !email.readOnly;
        const icon = editEmail.querySelector('i');

        if (email.readOnly) {
            icon.className = 'bi bi-pencil';
            editEmail.setAttribute('aria-label', 'Omogući ručnu izmenu email adrese');
            editEmail.title = 'Izmeni email adresu';
        } else {
            customEmail = true;
            icon.className = 'bi bi-lock';
            editEmail.setAttribute('aria-label', 'Zaključaj email adresu');
            editEmail.title = 'Zaključaj email adresu';
            email.focus();
            email.select();
        }
    });

    refreshStudentEmail();
}

const studyLevel = document.querySelector('#study_level');
const studyYear = document.querySelector('#study_year');

if (studyLevel && studyYear) {
    const refreshStudyYearLimit = () => {
        const maximumYear = studyLevel.value === 'master' ? 2 : 4;
        studyYear.max = maximumYear;

        if (Number(studyYear.value) > maximumYear) {
            studyYear.value = maximumYear;
        }
    };

    studyLevel.addEventListener('change', refreshStudyYearLimit);
    refreshStudyYearLimit();
}
