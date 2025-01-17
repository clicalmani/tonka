## Introduction

Cross-site request forgeries are a type of malicious exploit whereby unauthorized commands are performed on behalf of an authenticated user. Thankfully, Tonka makes it easy to protect your application from cross-site request forgery (CSRF) attacks.

## An Explanation of the Vulnerability

Cross-Site Request Forgery (CSRF) is a type of attack that occurs when a malicious actor tricks a user into performing actions on a web application in which they are authenticated. This can happen without the user's knowledge and can lead to unauthorized actions such as changing account details, making transactions, or other unwanted operations.

### How CSRF Works

1. **User Authentication**: The user logs into a web application and receives an authentication token (e.g., a session cookie).
2. **Malicious Request**: The attacker crafts a malicious request to the web application and embeds it in a link or form on a different website.
3. **User Interaction**: The user, while still authenticated, visits the attacker's website and unknowingly triggers the malicious request.
4. **Execution**: The web application processes the request as if it were made by the authenticated user, leading to unintended actions.

### Example Scenario

1. A user logs into their banking website and remains authenticated.
2. The user visits a malicious website created by an attacker.
3. The malicious website contains a hidden form that submits a request to transfer money from the user's account to the attacker's account.
4. When the user visits the malicious website, the form is automatically submitted, and the banking website processes the transfer request using the user's authenticated session.

### Mitigation Strategies

- **CSRF Tokens**: Include unique tokens in forms and verify them on the server side to ensure the request is legitimate.
- **SameSite Cookies**: Use the `SameSite` attribute for cookies to prevent them from being sent with cross-site requests.
- **Referer Header Validation**: Check the `Referer` header to ensure requests are coming from trusted sources.
- **User Interaction**: Require user interaction (e.g., re-entering a password) for sensitive actions.

By implementing these strategies, web applications can significantly reduce the risk of CSRF attacks.