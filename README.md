# WordPress SSO Login using Authress
The SSO Login plugin for Wordpress - adds SSO html template and login buttons

### Customizing the SSO Login plugin
[See customizations](./docs/customizations.md)

### Development

```sh
sudo apt install php-xmlwriter
composer install --no-progress --prefer-dist --optimize-autoloader
```

* [Using Subversion with the WordPress Plugin Directory](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/)
* [FAQ about the WordPress Plugin Directory](https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/)
* [WordPress Plugin Directory readme.txt standard](https://wordpress.org/plugins/developers/#readme)
* [A readme.txt validator](https://wordpress.org/plugins/developers/readme-validator/)
* [Plugin Assets (header images, etc)](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/)
* [WordPress Plugin Directory Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)
* [Block Specific Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/block-specific-plugin-guidelines/)

### Testing
To test login, navigate to `http://localhost:8080/wp-login.php`
* The default login credentials as `admin` - `admin`
Likewise the admin menu is at: `http://localhost:8080/wp-admin.php`
