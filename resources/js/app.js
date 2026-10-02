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

const initializeStudentEmailGenerator = () => {
    const studentEmailGenerator = document.querySelector('[data-student-email-generator]');

    if (!studentEmailGenerator) {
        return;
    }

    const form = studentEmailGenerator.closest('form');
    const firstName = form.querySelector('#first_name');
    const lastName = form.querySelector('#last_name');
    const indexPrefix = form.querySelector('#index_number_prefix');
    const indexSuffix = form.querySelector('#index_number_suffix');
    const email = form.querySelector('#email');
    const editEmail = form.querySelector('[data-email-edit]');
    const automaticEmail = studentEmailGenerator.dataset.automaticEmail === 'true';
    let customEmail = !automaticEmail || studentEmailGenerator.dataset.initialEmail.trim() !== '';
    let scheduledEmailRefresh = 0;

    const refreshStudentEmail = () => {
        // Ručno izmenjena adresa se više ne prepisuje automatskim predlogom.
        if (!automaticEmail || customEmail) {
            return;
        }

        const completed = firstName.value.trim() && lastName.value.trim() && indexPrefix.value && indexSuffix.value;
        const generatedEmail = completed
            ? `${[
                firstName.value,
                lastName.value,
                `${indexPrefix.value}-${indexSuffix.value}`,
            ].map(normalizeEmailPart).join('.')}@${studentEmailGenerator.dataset.emailDomain}`
            : '';

        // Podrazumevana vrednost čuva generisani email i pri osvežavanju stanja forme u pregledaču.
        email.defaultValue = generatedEmail;
        email.value = generatedEmail;
    };

    const updateStudentEmail = () => {
        refreshStudentEmail();
        cancelAnimationFrame(scheduledEmailRefresh);
        scheduledEmailRefresh = requestAnimationFrame(refreshStudentEmail);
    };

    [indexPrefix, indexSuffix].forEach((field) => {
        field.addEventListener('input', () => {
            field.value = field.value.replace(/\D/g, '');
            updateStudentEmail();
        });
        field.addEventListener('change', updateStudentEmail);
    });

    if (automaticEmail) {
        [firstName, lastName].forEach((field) => {
            field.addEventListener('input', updateStudentEmail);
            field.addEventListener('change', updateStudentEmail);
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

    email.addEventListener('input', () => {
        if (customEmail) {
            email.defaultValue = email.value;
        }
    });

    updateStudentEmail();
};

const initializeStudyYearLimit = () => {
    const studyLevel = document.querySelector('#study_level');
    const studyYear = document.querySelector('#study_year');

    if (!studyLevel || !studyYear) {
        return;
    }

    const refreshStudyYearLimit = () => {
        const maximumYear = studyLevel.value === 'master' ? 2 : 4;
        studyYear.max = maximumYear;

        if (Number(studyYear.value) > maximumYear) {
            studyYear.value = maximumYear;
        }
    };

    studyLevel.addEventListener('change', refreshStudyYearLimit);
    refreshStudyYearLimit();
};

const initializeForms = () => {
    initializeStudentEmailGenerator();
    initializeStudyYearLimit();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeForms, { once: true });
} else {
    initializeForms();
}
