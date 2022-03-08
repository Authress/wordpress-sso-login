== Customizations ==

The Authress SSO Login comes with default functions and template to enable login. These extension points can be intercepted and altered as you see fit.

==== Login Screen Template ====
The open source default version of the template is available at [./templates/authress-login-form.php]. You can use this for inspiration to make your own, the Authress login SDK will be injected in.

```php
function get_login_template($authress_default_template, $options) {
    // $options properties:
    // $options = [
    //     'custom_domain' => 'https://your-domain.com',
    //     'application_id' => 'app_authress_application_id'
    // ];
    // generate a new template
    return $new_template;
}
add_filter('authress::user_login_template::html::formatter', 'get_login_template');
```

=== Notice ===
Make sure to only use the documented properties for improved reliability and backwards compatibility with future versions of the plugin. Using classes such as `Authress_Sso_Login_Options::Instance()` is not permitted and it's usage is not stable. If you need access to additional parameters, please file a support ticket with the Authrss Development team.