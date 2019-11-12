# Internet check

> Checks for internet connectivity and restarts router after a grace period to restore connectivity. 

## Install

```bash
git clone https://github.com/alexanderglueck/internet-check.git
cd internet-check
composer install
npm install
```

## Usage

Adjust the constants in `src/InternetCheck.php` and `src/restart.js` to change your credentials.

Either set up a cronjob to run the init script every minute or manually call the script when your internet is down.

```bash
php check.php
```

## Security

If you discover a security vulnerability within this application, please send an e-mail to Alexander Glück at security@alexanderglueck.at. 
All security vulnerabilities will be promptly addressed.

Please do not open an issue describing the vulnerability. 


## Maintainers

[@alexanderglueck][maintainer-alexanderglueck]

## Contribute

Feel free to dive in! Open an issue or submit PRs.

## License

[MIT](https://opensource.org/licenses/MIT)

[maintainer-alexanderglueck]: https://github.com/alexanderglueck
