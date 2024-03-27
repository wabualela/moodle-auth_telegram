**Moodle Authentication Plugin with Telegram Login Widget**

Welcome to the Moodle Authentication Plugin with Telegram Login Widget! This plugin allows you to integrate Telegram's secure login functionality directly into your Moodle platform, providing users with a convenient and secure authentication method. This README provides instructions on how to install the plugin, configure Telegram authentication settings, and set up a Telegram bot for authentication purposes.

**Installation Steps:**

1. **Download the Plugin:**
   - Clone the repository or download the plugin ZIP file from the GitHub repository.

2. **Upload to Moodle:**
   - Upload the plugin folder to the `auth` directory in your Moodle installation directory.

3. **Install the Plugin:**
   - Log in to your Moodle site as an administrator.
   - Navigate to **Site Administration** > **Notifications**.
   - Moodle will detect the new plugin and prompt you to install it. Follow the on-screen instructions to complete the installation process.

4. **Enable Telegram Authentication:**
   - After installation, navigate to **Site Administration** > **Plugins** > **Authentication** > **Manage authentication**.
   - Enable the "Telegram" authentication method.

5. **Configure Telegram Authentication:**
   - Click on **Settings** next to the Telegram authentication method.
   - Enter your Telegram bot's username in the designated field.
   - Save the settings.

**Setting Up Telegram Bot:**

1. **Create a Telegram Bot:**
   - Open the Telegram app and search for the "BotFather" user.
   - Start a conversation with BotFather and follow the prompts to create a new bot.
   - Note down the bot's username provided by BotFather.

2. **Obtain Bot Token:**
   - After creating the bot, BotFather will provide you with a token. Note down this token as it will be used to authenticate your bot with Telegram's API.

3. **Add Domain to Bot:**
   - To restrict bot access to your domain, navigate to your bot's settings in BotFather.
   - Add your Moodle site's domain to the allowed domains list.

4. **Configure Moodle Plugin:**
   - In the Moodle plugin settings, enter your bot's username in the designated field.

**Usage:**

Once the plugin is installed and configured, users can log in to Moodle using their Telegram accounts. They will be prompted to authorize the Telegram bot for authentication purposes. After authorization, users can seamlessly log in to Moodle using their Telegram credentials.

**Feedback and Support:**

If you encounter any issues during installation or have any questions about the plugin, please don't hesitate to reach out to our support team or submit a GitHub issue. We value your feedback and are committed to providing ongoing support to ensure a smooth experience with our plugin.

Thank you for choosing our Moodle Authentication Plugin with Telegram Login Widget. We hope this plugin enhances your Moodle platform's authentication experience for both administrators and users alike.
