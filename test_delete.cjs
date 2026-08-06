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
    page.on('requestfailed', (req) => errors.push('REQFAIL: ' + req.url() + ' ' + ((req.failure() || {}).errorText || '')));
    page.on('response', (res) => {
        if (res.status() >= 400) errors.push('HTTP ' + res.status() + ': ' + res.url());
    });

    async function login(login, pw) {
        await page.goto('http://localhost/iseki_recorder/public/login', { waitUntil: 'networkidle0' });
        await page.click('#tabMember');
        await page.type('#login', login);
        await page.type('#password', pw);
        const loginBtns = await page.$$('button');
        let loginBtn = null;
        for (const b of loginBtns) {
            if (await b.evaluate((el) => el.innerText.trim() === 'Masuk')) { loginBtn = b; break; }
        }
        await Promise.all([
            loginBtn.click(),
            page.waitForNavigation({ waitUntil: 'networkidle0' }),
        ]);
    }

    await login('130203', 'ms9');

    // re-upload a photo so folder 11 has one to delete (idempotent-ish)
    const before = await page.evaluate(() => document.querySelectorAll('.photo-check').length);
    console.log('photo count on folder page BEFORE recheck:', before);

    await page.goto('http://localhost/iseki_recorder/public/member/folders/11', { waitUntil: 'networkidle0' });
    const hasForm = await page.evaluate(() => document.querySelector('.photo-delete-form') !== null);
    console.log('delete form present:', hasForm);

    const btnInfo = await page.evaluate(() => {
        const btn = document.querySelector('.photo-delete-form button[type="submit"]');
        if (!btn) return { found: false };
        const r = btn.getBoundingClientRect();
        const el = document.elementFromPoint(r.x + r.width / 2, r.y + r.height / 2);
        return {
            found: true,
            rect: { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) },
            topEl: el ? el.tagName + '.' + String(el.className || '').split(' ')[0] : 'none',
            clickHitsBtn: el === btn || (el && btn.contains(el)),
        };
    });
    console.log('BUTTON HIT-TEST:', JSON.stringify(btnInfo));

    page.on('dialog', async (dialog) => {
        console.log('DIALOG:', dialog.message());
        await dialog.accept();
    });

    const photosBefore = await page.evaluate(() => document.querySelectorAll('.photo-check').length);
    console.log('photos before click:', photosBefore);

    const target = await page.$$('.photo-delete-form button[type="submit"]');
    await Promise.all([
        target[0].click(),
        page.waitForNavigation({ waitUntil: 'networkidle0' }),
    ]);

    const photosAfter = await page.evaluate(() => document.querySelectorAll('.photo-check').length);
    console.log('photos after click:', photosAfter);
    console.log('URL after delete:', page.url());
    console.log('ERRORS:', JSON.stringify(errors, null, 2));

    await browser.close();
})();
