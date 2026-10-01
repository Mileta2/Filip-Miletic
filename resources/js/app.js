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
    const firstName = document.querySelector('#first_name');
    const lastName = document.querySelector('#last_name');
    const indexNumber = document.querySelector('#index_number');
    const email = document.querySelector('#email');
    const emailDomain = studentEmailGenerator.dataset.emailDomain;
    let lastGeneratedEmail = email.value;

    const refreshStudentEmail = () => {
        const parts = [firstName.value, lastName.value, indexNumber.value].map(normalizeEmailPart);
        const generatedEmail = parts.every(Boolean) ? `${parts.join('.')}@${emailDomain}` : '';

        // ručno izmenjena adresa se više ne prepisuje automatskim predlogom
        if (email.value === '' || email.value === lastGeneratedEmail) {
            email.value = generatedEmail;
            lastGeneratedEmail = generatedEmail;
        }
    };

    [firstName, lastName, indexNumber].forEach((field) => field.addEventListener('input', refreshStudentEmail));
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
