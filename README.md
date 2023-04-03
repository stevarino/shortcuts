# shortcuts

A quick custom-link system with minimal dependencies utilizing Google OAuth.

Given a root url of `https://shortcuts.example.com` and a configured mapping of `wapo` -> `https://washingtonpost.com`, pointing a browser at `https://shortcuts.example.com/wapo` will redirect to `https://washingtonpost.com`. This is intended as a private version of [corporate-style go-links](https://golinks.medium.com/silicon-valleys-biggest-secret-the-golink-7b42d93bc8c4).

This is very useful for referencing resources with cumbersome domains (GitHub gists, Google Docs, discord channels) and deep-linking commonly cited resources.

<img src='https://raw.githubusercontent.com/stevarino/shortcuts/main/screenshot.png' alt='Application Screenshot' width='50%' align='right' />

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
