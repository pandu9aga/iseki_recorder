const puppeteer = require('puppeteer-core');

(async () => {
    const browser = await puppeteer.launch({
        executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
        headless: 'new',
        args: ['--no-sandbox', '--disable-gpu'],
    });
    const page = await browser.newPage();
    const errors = [];
    page.on('console', (msg) => { if (msg.type() === 'error') errors.push('CONSOLE: ' + msg.text()); });
    page.on('pageerror', (err) => errors.push('PAGEERROR: ' + err.message));
    page.on('response', (res) => { if (res.status() >= 400) errors.push('HTTP ' + res.status() + ': ' + res.url()); });

    await page.goto('http://localhost/iseki_recorder/public/login', { waitUntil: 'networkidle0' });
    await page.type('#login', 'admin');
    await page.type('#password', 'admin');
    const loginBtns = await page.$$('button');
    let loginBtn = null;
    for (const b of loginBtns) {
        if (await b.evaluate((el) => el.innerText.trim() === 'Masuk')) { loginBtn = b; break; }
    }
    await Promise.all([loginBtn.click(), page.waitForNavigation({ waitUntil: 'networkidle0' })]);

    await page.goto('http://localhost/iseki_recorder/public/admin/folders/4', { waitUntil: 'networkidle0' });

    // count photos and find the form for photo id 15
    const photoCount = await page.evaluate(() => document.querySelectorAll('.photo-check').length);
    console.log('photos on folder 4:', photoCount);

    const formAction = await page.evaluate(() => {
        const forms = Array.from(document.querySelectorAll('form.photo-delete-form'));
        const f = forms.find((x) => x.getAttribute('action').includes('/photos/15'));
        return f ? f.getAttribute('action') : null;
    });
    console.log('delete form for photo 15:', formAction);

    page.on('dialog', async (dialog) => { console.log('DIALOG:', dialog.message()); await dialog.accept(); });

    const form = await page.evaluateHandle(() => {
        const forms = Array.from(document.querySelectorAll('form.photo-delete-form'));
        return forms.find((x) => x.getAttribute('action').includes('/photos/15'));
    });
    const submitted = await form.asElement().evaluate((f) => f.requestSubmit());
    await page.waitForNavigation({ waitUntil: 'networkidle0' }).catch(() => {});
    await page.waitForTimeout(1500);

    const photoCountAfter = await page.evaluate(() => document.querySelectorAll('.photo-check').length);
    console.log('photos after delete photo 15:', photoCountAfter);
    console.log('URL:', page.url());
    console.log('ERRORS:', JSON.stringify(errors, null, 2));

    await browser.close();
})();
