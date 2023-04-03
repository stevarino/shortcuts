# shortcuts

A quick custom-link system with minimal dependencies utilizing Google OAuth.

<center>![Application Screenshot](/screenshot.png)</center>

## Requirements

 - PHP with mod-rewrite support (or similar - path is passed in via `?link=...` querystring)
 - Goolge Cloud project with OAuth Web Application Credentials.

## Configuration

Copy `app.example.json` to `app.json` and fill in the following settings:

 - url: must equal the redirect URL specified in the OAuth project. Used as the root URL.
 - client_id: The client_id field from the OAuth credentials.
 - client_secret: The client_secret field from the OAuth credentials.
 - auth: List of email addresses to be given authorization. One is required to use the web interface.
 - links: key/value setting of links.
