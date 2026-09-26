import { test, expect } from '@playwright/test';

const accounts = {
  superAdmin: 'super-admin@coor.test',
  hospitalAdmin: 'hospital.admin@coor.test',
  registration: 'registration@coor.test',
  doctor: 'doctor@coor.test',
  nurse: 'nurse@coor.test',
  patient: 'patient@coor.test',
};
const password = 'Password123!';

async function login(page, email) {
  await page.goto('/login');
  await page.locator('#login-email').fill(email);
  await page.locator('#login-password').fill(password);
  await page.getByRole('button', { name: /sign in/i }).click();
  await expect(page).not.toHaveURL(/\/login/);
}

async function logout(page) {
  const profileToggle = page.locator('#profile-toggle');
  if (await profileToggle.count()) {
    await profileToggle.click();
  }
  const logout = page.locator('form[action*="logout"] button, a[href*="logout"]').first();
  if (await logout.count()) {
    await logout.click();
    await expect(page).toHaveURL(/login|\/$/);
  }
}

test.describe('role and appointment access', () => {
  test('super-admin sees no removed Hospital Admin role', async ({ page }) => {
    await login(page, accounts.superAdmin);
    for (const path of ['/dashboard', '/patients', '/appointments', '/emergency', '/admissions']) {
      const response = await page.goto(path);
      expect(response?.status(), path).toBeLessThan(500);
      await expect(page.locator('body')).not.toContainText(/Hospital Admin/i);
    }
  });

  test('appointment creation is allowed for registration and denied for other roles', async ({ page }) => {
    await login(page, accounts.registration);
    const allowed = await page.goto('/appointments/create');
    expect(allowed?.status()).toBe(200);
    await expect(page).toHaveURL(/appointments\/create/);

    for (const email of [accounts.doctor, accounts.nurse, accounts.patient]) {
      await page.context().clearCookies();
      await login(page, email);
      const response = await page.goto('/appointments/create');
      expect([302, 403]).toContain(response?.status());
      if (response?.status() === 302) {
        await expect(page).not.toHaveURL(/appointments\/create/);
      }
    }
  });
});

test.describe('public and authenticated pre-registration', () => {
  test('newly registered unverified users see the verify-email screen and can resend the verification email', async ({ page }) => {
    const unique = Date.now();
    const name = `Verify User ${unique}`;
    const email = `verify.${unique}@example.test`;

    await page.goto('/register');
    await page.locator('#name').fill(name);
    await page.locator('#email').fill(email);
    await page.locator('#password').fill(password);
    await page.locator('#password_confirmation').fill(password);
    await page.getByRole('button', { name: /create account/i }).click();

    await expect(page).toHaveURL(/\/verify-email/);
    await expect(page.locator('body')).toContainText(/Verify Your Email Address|Resend Verification Email/i);

    await page.getByRole('button', { name: /resend verification email/i }).click();
    await expect(page.locator('body')).toContainText(/A new verification link has been sent/i);
  });

  test('public pre-registration form exposes all expected demographic and address fields', async ({ page }) => {
    await page.goto('/pre-register');

    const fieldNames = [
      'input[name="first_name"]',
      'input[name="middle_name"]',
      'input[name="last_name"]',
      'input[name="suffix"]',
      'input[name="date_of_birth"]',
      'select[name="sex"]',
      'input[name="civil_status"]',
      'input[name="nationality"]',
      'input[name="phone"]',
      'input[name="email"]',
      'input[name="address_line1"]',
      'input[name="address_province"]',
      'input[name="address_city"]',
      'input[name="address_barangay"]',
      'input[name="address_postal"]',
      'input[name="emergency_name"]',
      'input[name="emergency_relationship"]',
      'input[name="emergency_phone"]',
      'textarea[name="allergies"]',
    ];

    for (const field of fieldNames) {
      await expect(page.locator(field)).toBeVisible();
    }
  });

  test('walk-in registration form exposes the same demographic fields as pre-registration', async ({ page }) => {
    await login(page, accounts.registration);
    await page.goto('/patients');
    await page.getByRole('button', { name: 'Register Patient' }).click();

    const fieldNames = [
      'input[name="first_name"]',
      'input[name="middle_name"]',
      'input[name="last_name"]',
      'input[name="suffix"]',
      'input[name="date_of_birth"]',
      'select[name="sex"]',
      'input[name="civil_status"]',
      'input[name="nationality"]',
      'input[name="phone"]',
      'input[name="email"]',
      'input[name="address_line1"]',
      'input[name="address_province"]',
      'input[name="address_city"]',
      'input[name="address_barangay"]',
      'input[name="address_postal"]',
      'input[name="emergency_name"]',
      'input[name="emergency_relationship"]',
      'input[name="emergency_phone"]',
      'textarea[name="allergies"]',
    ];

    for (const field of fieldNames) {
      await expect(page.locator('#registerPatientModal form[action*="patients"] ' + field)).toBeVisible();
    }
  });

  test('reference lookup response exposes DOB and sex to the registration form', async ({ page }) => {
    const unique = Date.now();
    const patient = {
      firstName: 'Lookup',
      lastName: `Ref${unique}`,
      birthDate: '1994-04-14',
      sex: 'Female',
    };

    await page.goto('/pre-register');
    await page.locator('input[name="first_name"]').fill(patient.firstName);
    await page.locator('input[name="last_name"]').fill(patient.lastName);
    await page.locator('input[name="date_of_birth"]').fill(patient.birthDate);
    await page.locator('select[name="sex"]').selectOption(patient.sex);
    await page.getByRole('button', { name: /submit pre-registration/i }).click();

    await expect(page.getByText(/PAC-\d{4}/).first()).toBeVisible();
    const reference = (await page.getByText(/PAC-\d{4}/).first().textContent())?.trim();
    expect(reference).toMatch(/^PAC-\d{4}$/);

    await login(page, accounts.registration);
    await page.goto('/patients');
    await page.getByRole('button', { name: 'Register Patient' }).click();

    const lookupPayload = await page.evaluate(async (ref) => {
      const response = await fetch(`/patients/lookup?q=${encodeURIComponent(ref)}`, {
        headers: { Accept: 'application/json' },
      });
      return response.json();
    }, reference);

    expect(lookupPayload.data?.[0]?.date_of_birth).toBe(patient.birthDate);
    expect(lookupPayload.data?.[0]?.sex).toBe(patient.sex);

    const dobField = page.locator('#registerPatientModal form[action*="patients"] input[name="date_of_birth"]');
    const sexField = page.locator('#registerPatientModal form[action*="patients"] select[name="sex"]');
    await expect(dobField).toBeVisible();
    await expect(sexField).toBeVisible();
  });

  test('new patient pre-registers publicly and staff confirms the same data', async ({ page }) => {
    const unique = Date.now();
    const patient = {
      firstName: 'E2E',
      middleName: 'Browser',
      lastName: `Patient${unique}`,
      birthDate: '1994-04-14',
      phone: '0917' + String(unique).slice(-7),
      email: `e2e.${unique}@example.test`,
      street: '99 Playwright Street',
      barangay: 'Test Barangay',
      city: 'Manila',
      province: 'Metro Manila',
      postal: '1000',
      emergencyName: 'E2E Contact',
      emergencyRelationship: 'Sibling',
      emergencyPhone: '09171234567',
      allergies: 'Latex',
    };

    await page.goto('/pre-register');
    await page.locator('input[name="first_name"]').fill(patient.firstName);
    await page.locator('input[name="last_name"]').fill(patient.lastName);
    await page.locator('input[name="middle_name"]').fill(patient.middleName);
    await page.locator('input[name="date_of_birth"]').fill(patient.birthDate);
    await page.locator('select[name="sex"]').selectOption('Female');
    await page.locator('input[name="phone"]').fill(patient.phone);
    await page.locator('input[name="email"]').fill(patient.email);
    await page.locator('input[name="address_line1"]').fill(patient.street);
    await page.locator('input[name="address_barangay"]').fill(patient.barangay);
    await page.locator('input[name="address_city"]').fill(patient.city);
    await page.locator('input[name="address_province"]').fill(patient.province);
    await page.locator('input[name="address_postal"]').fill(patient.postal);
    await page.locator('input[name="emergency_name"]').fill(patient.emergencyName);
    await page.locator('input[name="emergency_relationship"]').fill(patient.emergencyRelationship);
    await page.locator('input[name="emergency_phone"]').fill(patient.emergencyPhone);
    await page.locator('textarea[name="allergies"]').fill(patient.allergies);
    await page.locator('textarea[name="allergies"]').fill(patient.allergies);
    await page.getByRole('button', { name: /submit pre-registration/i }).click();

    await expect(page).toHaveURL(/pre-register/);
    const reference = await page.getByText(/PAC-\d{4}/).first().textContent();
    expect(reference).toMatch(/^PAC-\d{4}$/);

    await login(page, accounts.nurse);
    await page.goto('/emergency');
    await page.getByRole('button', { name: 'Pre-arrival lookup' }).click();
    await page.locator('#reference_code').fill(reference);
    await page.locator('form[action*="check-in/reference"] button[type="submit"]').click();
    await expect(page).toHaveURL(/emergency\/check-in/);

    await expect(page.locator('body')).toContainText(patient.firstName);
    await expect(page.locator('body')).toContainText(patient.lastName);
    await expect(page.locator('body')).toContainText(patient.birthDate);
    await expect(page.locator('body')).toContainText(patient.street);
    await expect(page.locator('body')).toContainText(patient.barangay);
    await expect(page.locator('body')).toContainText(patient.city);
    await expect(page.locator('body')).toContainText(patient.province);
    await expect(page.locator('body')).toContainText(patient.emergencyName);
    await expect(page.locator('body')).toContainText(patient.emergencyRelationship);
    await expect(page.locator('body')).toContainText(patient.emergencyPhone);
    await expect(page.locator('body')).toContainText(patient.allergies);

    await page.getByRole('button', { name: 'Confirm Registration' }).click();
    await expect(page).toHaveURL(/patients\/\d+/);
    await expect(page.locator('body')).toContainText(/registration was confirmed/i);
    await expect(page.locator('body')).toContainText(patient.firstName);
    await expect(page.locator('body')).toContainText(patient.lastName);
    await expect(page.locator('body')).toContainText(patient.street);
    await expect(page.locator('body')).toContainText(patient.emergencyName);
    await expect(page.locator('body')).not.toContainText(/validation errors|required field/i);
  });

  test('existing authenticated patient can submit portal pre-registration', async ({ page }) => {
    await login(page, accounts.patient);
    await page.goto('/portal/pre-register');
    await page.locator('input[name="first_name"]').fill('Maria');
    await page.locator('input[name="last_name"]').fill('Santos');
    await page.locator('input[name="date_of_birth"]').fill('1987-04-18');
    await page.locator('select[name="sex"]').selectOption('Female');
    await page.getByRole('button', { name: /save pre-registration/i }).click();
    await expect(page).toHaveURL(/patient-portal/);
    await expect(page.getByText(/PAC-\d{4}/).first()).toBeVisible();
  });
});

test.describe('walk-in and ER conversion', () => {
  test('hospital.admin@coor.test retains super-admin dashboard access', async ({ page }) => {
    await login(page, accounts.hospitalAdmin);
    const dashboardResponse = await page.goto('/dashboard');
    expect(dashboardResponse?.status()).toBe(200);
    await expect(page).toHaveURL(/\/dashboard/);
    await expect(page.locator('body')).toContainText(/Operational snapshot for today|Care coordination overview/i);

    const auditLogsResponse = await page.goto('/audit-logs');
    expect(auditLogsResponse?.status()).toBe(200);
  });

  test('duplicate patient registration is blocked and stays on /patients', async ({ page }) => {
    const unique = Date.now();
    const uniqueLetters = Array.from(String(unique), digit => String.fromCharCode(65 + Number(digit))).join('');
    const duplicateDetails = {
      firstName: 'Duplicate',
      lastName: `Patient${uniqueLetters}`,
      dob: '1988-02-10',
      phone: '0917' + String(unique).slice(-7),
    };

    await login(page, accounts.registration);
    await page.goto('/patients');
    await page.getByRole('button', { name: 'Register Patient' }).click();

    const registrationForm = page.locator('#registerPatientModal form[action*="patients"]');
    await registrationForm.locator('input[name="first_name"]').fill(duplicateDetails.firstName);
    await registrationForm.locator('input[name="last_name"]').fill(duplicateDetails.lastName);
    await registrationForm.locator('input[name="date_of_birth"]').fill(duplicateDetails.dob);
    await registrationForm.locator('select[name="sex"]').selectOption('Male');
    await registrationForm.locator('input[name="phone"]').fill(duplicateDetails.phone);
    await registrationForm.locator('input[name="address_line1"]').fill('1 Duplicate Road');
    await registrationForm.locator('input[name="address_province"]').fill('Metro Manila');
    await registrationForm.locator('input[name="address_city"]').fill('Manila');
    await registrationForm.locator('input[name="address_barangay"]').fill('Barangay Duplicate');
    await registrationForm.locator('input[name="emergency_name"]').fill('Duplicate Contact');
    await registrationForm.locator('input[name="emergency_phone"]').fill('09178889999');
    await registrationForm.locator('input[name="emergency_relationship"]').fill('Spouse');
    await registrationForm.getByRole('button', { name: 'Register Patient' }).click();
    await expect(page).toHaveURL(/\/patients\/\d+$/);

    await page.goto('/patients');
    await page.getByRole('button', { name: 'Register Patient' }).click();
    const duplicateForm = page.locator('#registerPatientModal form[action*="patients"]');
    await duplicateForm.locator('input[name="first_name"]').fill(duplicateDetails.firstName);
    await duplicateForm.locator('input[name="last_name"]').fill(duplicateDetails.lastName);
    await duplicateForm.locator('input[name="date_of_birth"]').fill(duplicateDetails.dob);
    await duplicateForm.locator('select[name="sex"]').selectOption('Male');
    await duplicateForm.locator('input[name="phone"]').fill(duplicateDetails.phone);
    await duplicateForm.locator('input[name="address_line1"]').fill('2 Duplicate Road');
    await duplicateForm.locator('input[name="address_province"]').fill('Metro Manila');
    await duplicateForm.locator('input[name="address_city"]').fill('Manila');
    await duplicateForm.locator('input[name="address_barangay"]').fill('Barangay Duplicate');
    await duplicateForm.locator('input[name="emergency_name"]').fill('Duplicate Contact');
    await duplicateForm.locator('input[name="emergency_phone"]').fill('09178889999');
    await duplicateForm.getByRole('button', { name: 'Register Patient' }).click();

    await expect(page).toHaveURL(/\/patients(?:\/)?$/);
    await expect(page.locator('body')).toContainText(/Possible existing patient record found|Possible matches/i);
  });

  test('registration staff can complete a true walk-in registration', async ({ page }) => {
    const unique = Date.now();
    const uniqueLetters = Array.from(String(unique), digit => String.fromCharCode(65 + Number(digit))).join('');
    const lastName = `BrowserWalkIn${uniqueLetters}`;
    await login(page, accounts.registration);
    await page.goto('/patients');
    await page.getByRole('button', { name: 'Register Patient' }).click();
    const registrationForm = page.locator('#registerPatientModal form[action*="patients"]');
    await registrationForm.locator('input[name="first_name"]').fill('WalkIn');
    await registrationForm.locator('input[name="last_name"]').fill(lastName);
    await registrationForm.locator('input[name="date_of_birth"]').fill('1991-01-01');
    await registrationForm.locator('select[name="sex"]').selectOption('Male');
    await registrationForm.locator('input[name="phone"]').fill('0917' + String(unique).slice(-7));
    await registrationForm.locator('input[name="address_line1"]').fill('1 Walk In Road');
    await registrationForm.locator('input[name="address_province"]').fill('Metro Manila');
    await expect(registrationForm.locator('input[name="address_city"]')).toBeEnabled();
    await registrationForm.locator('input[name="address_city"]').fill('Manila');
    await expect(registrationForm.locator('input[name="address_barangay"]')).toBeEnabled();
    await registrationForm.locator('input[name="address_barangay"]').fill('Barangay Walk');
    await registrationForm.locator('input[name="emergency_name"]').fill('Walk In Contact');
    await registrationForm.locator('input[name="emergency_phone"]').fill('09178888888');
    await registrationForm.locator('input[name="emergency_relationship"]').fill('Parent');
    await registrationForm.getByRole('button', { name: 'Register Patient' }).click();
    await expect(page).toHaveURL(/\/patients\/\d+$/);
    await expect(page.locator('body')).toContainText(/MRN-\d{4}-\d{6}/);
    await expect(page.locator('body')).toContainText(lastName);
  });

  test('nurse can triage an ER visit and convert it to admission', async ({ page }) => {
    await login(page, accounts.nurse);
    await page.goto('/emergency');
    await page.getByRole('button', { name: 'New ER Intake' }).click();
    const intake = page.locator('#intakeModal');
    await intake.locator('select[name="patient_id"]').selectOption({ index: 1 });
    await intake.locator('textarea[name="chief_complaint"]').fill('E2E admission conversion complaint');
    await intake.getByRole('button', { name: /run ai triage/i }).click();
    await expect(intake.locator('#aiResult')).toBeVisible();
    await intake.getByRole('button', { name: /confirm & add to queue/i }).click();
    await expect(page).toHaveURL(/emergency\/\d+/);

    await page.getByRole('button', { name: /confirm priority to add/i }).click({ force: true });
    const triage = page.locator('#triageQueueModal');
    await triage.locator('select[name="priority"]').selectOption('Level 3');
    await triage.locator('#ai-confirmed-toggle').check();
    await triage.getByRole('button', { name: /confirm priority/i }).click();
    await expect(page.locator('body')).toContainText(/queued|priority/i);

    await page.getByRole('link', { name: 'Convert to Admission' }).click();
    await expect(page).toHaveURL(/admission\/create/);
    await page.locator('input[name="reason"]').fill('E2E inpatient observation');
    await page.getByRole('button', { name: /create admission/i }).click();
    await expect(page).toHaveURL(/admissions\/\d+/);
    await expect(page.locator('body')).toContainText(/admission/i);
    await expect(page.locator('body')).toContainText(/E2E inpatient observation/);

    const erLink = page.getByRole('link', { name: /ER visit|Emergency/i }).first();
    if (await erLink.count()) {
      await erLink.click();
      await expect(page.locator('body')).toContainText(/ADMITTED/i);
    }
  });
});
