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
    page.on('requestfailed', (req) => errors.push('REQFAIL: ' + req.url() + ' ' + (req.failure() || {}).errorText));
    page.on('response', (res) => {
        if (res.status() >= 400) errors.push('HTTP ' + res.status() + ': ' + res.url());
    });

    // login
    await page.goto('http://localhost/iseki_recorder/public/login', { waitUntil: 'networkidle0' });
    await page.type('#login', '130203');
    await page.type('#password', 'ms9');
    await page.click('#tabMember');
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });

    // go to folder 11
    await page.goto('http://localhost/iseki_recorder/public/member/folders/11', { waitUntil: 'networkidle0' });

    const btnInfo = await page.evaluate(() => {
        const btn = document.querySelector('.photo-delete-form button[type="submit"]');
        if (!btn) return { found: false };
        const r = btn.getBoundingClientRect();
        const el = document.elementFromPoint(r.x + r.width / 2, r.y + r.height / 2);
        return {
            found: true,
            topEl: el ? el.tagName + '.' + (el.className || '').split(' ')[0] : 'none',
            isBtn: el === btn || btn.contains(el),
        };
    });
    console.log('BUTTON HIT-TEST:', JSON.stringify(btnInfo));

    page.on('dialog', async (dialog) => {
        console.log('DIALOG:', dialog.message());
        await dialog.accept();
    });

    const before = await page.evaluate(() => document.querySelectorAll('.photo-check').length);
    console.log('photos before click:', before);

    await page.click('.photo-delete-form button[type="submit"]');
    await page.waitForTimeout(2500);

    const after = await page.evaluate(() => document.querySelectorAll('.photo-check').length);
    console.log('photos after click:', after);
    console.log('URL now:', page.url());
    console.log('ERRORS:', JSON.stringify(errors, null, 2));

    await browser.close();
})();
