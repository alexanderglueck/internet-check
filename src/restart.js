const puppeteer = require('puppeteer');

const chromiumExecutable = '/usr/bin/chromium-browser';

const username = 'admin';
const password = '';

const pages = {
    login: 'http://10.0.0.138/ui/login',
    expert: 'http://10.0.0.138/ui/dboard?level=2',
    reboot: 'http://10.0.0.138/ui/dboard/system/reboot?backto=system',
};

const selectors = {
    fields: {
        username: 'input[name="userName"]',
        password: 'input[name="origUserPwd"]',
    },
    buttons: {
        login: 'input[name="login"]',
        reboot: 'input[name="reboot"]'
    }
};

(async () => {
    // Basic setup
    const browser = await puppeteer.launch({
        executablePath: chromiumExecutable,
        userDataDir: './chrome_data'
    });
    const page = await browser.newPage();

    // Go to login page
    await page.goto(pages.login, {waitUntil: 'networkidle2'});

    // Empty username field and input username
    await page.evaluate(({selectors}) => {
        document.querySelector(selectors.fields.username).value = ""
    }, {selectors})
    await page.type(selectors.fields.username, username);

    // Empty password field and input password
    await page.evaluate(({selectors}) => {
        document.querySelector(selectors.fields.password).value = ""
    }, {selectors})
    await page.type(selectors.fields.password, password);

    // Login
    const [loginResponse] = await Promise.all([
        page.waitForNavigation(),
        page.click(selectors.buttons.login),
    ]);

    if (!loginResponse.ok()) {
        await browser.close();
        return;
    }

    // We are now logged in with basic permissions

    // Go to expert level page
    await page.goto(pages.expert, {waitUntil: 'networkidle2'});

    // Go to reboot page
    await page.goto(pages.reboot, {waitUntil: 'networkidle2'});

    const [rebootResponse] = await Promise.all([
        page.waitForNavigation(),
        page.click(selectors.buttons.reboot),
    ]);

    if (!rebootResponse.ok()) {
        await browser.close();
    }

    // Router is now restarting. The reboot button does not immediately navigate away from the page.
    // Navigation occurs if the loading bar has been completed.

    await browser.close();
})();