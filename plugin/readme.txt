=== Authress ===
Contributors: authress
Tags: authentication, user login, SSO, security, multi-factor, single sign-on, social, passwordless, mfa, 2fa, oauth2, openid, saml
Requires at least: 5.5
Requires PHP: 7.3
Tested up to: 5.8
Stable tag: 0.1
License: Apache-2.0
License URI: https://github.com/Authress/wordpress-plugin/blob/main/LICENSE

Login by Authress provides user login, business authentication, SSO, Social login, and Single Sign-On for all sites.

== Description ==

This plugin replaces standard WordPress login forms with one powered by [Authress](https://authress.io) that enables:

- **Universal authentication**
    - Over 40 social login providers
    - Enterprise connections (SAML, Office 365, Google Apps, and more)
    - Customer configurable SSO connections
- **Ultra secure**
    - User identity
    - Security access policies
    - Mitigate brute force attacks

== Installation ==

This plugin requires an [Authress account](https://authress.io).

1. [Sign up here](https://authress.io/app/#/signup).
2. Follow the installation instructions below.

== Technical Notes ==

**IMPORTANT**: By using this plugin you are delegating the site authentication and profile handling to Authress. That means that you won't need to use the WordPress database to authenticate users and the default WordPress login forms will be upgraded to support this.

Please see our [knowledge base](https://authress.io/knowledge-base/) for more information on how Authress authenticates and manages your users.

= Migrating Existing Users =

Authress allows multiple authentication providers. You can have social providers like Facebook, Twitter, Google+, a database of users and passwords (just like WordPress), or you can use an Enterprise directories like, Office365, Google Apps, SAML, OpenID, OAuth2.1. All those authentication providers are supported and more.

= Widget =

You can enable the Authress as a WordPress widget in order to show it in a sidebar. The widget inherits the main plugin settings but can be overridden with its own settings in the widget form. Note: this form will not display for logged-in users.

= Shortcode =

Also, you can use the Authress widget as a shortcode in your editor. Just add the following to use the global settings:

    [authress]

Like widgets, shortcode login forms will use the main plugins settings. It can be customized by adding the following attributes:

- `icon_url` - A direct URL to an image used at the top of the login form
- `title` - Text to appear at top of the login form
- `redirect_url` - A direct URL to use after successful login
- `options` - Valid JSON configuration to specify custom options ([see options here](https://authress.io))

Example:

    [authress icon_url="https://your-app.example.com/logo.png"]

== Frequently Asked Questions ==

= Can I customize the Authress login? =

The Authress login widget is completely configurable and it's [open source on GitHub](https://github.com/Authress/wordpress-plugin). You can style the form like any of your site components by enqueuing a stylesheet in your theme. Use the [`login_enqueue_scripts`](https://developer.wordpress.org/reference/hooks/login_enqueue_scripts/) hook to style the form on wp-login.php, [`wp_enqueue_scripts`](https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/) to style widgets and shortcodes, or both to affect the form in all locations.

= Can I access the user profile information? =

The Authress plugin transparently handles login information for your WordPress site and the plugins you use, so that it looks like any other login. User profile data changes in WordPress **are not** currently sent to Auth0 but changes to the Auth0 user account **are** stored in WordPress user meta (under the key `auth0_obj` prefixed with `$wpdb->prefix`).

= Which authentication providers are supported? =

All social providers and business login directories are supported. For more details, please see [configuring social and enterprise authentication providers](https://authress.io/knowledge-base/user-oauth-authentication-quick-start).


= Is this plugin compatible with WooCommerce? =

Yes, this plugin will override the default WooCommerce login and all other WordPress compatible login widgets.

= Didn't find what you were looking for? =

No problem, you can directly connect with the Authress development team in our [user community](https://authress.io/community), and we'll help you get squared away.

== Changelog ==

[Complete list of changes for this and other releases](https://github.com/Authress/wordpress-plugin)
