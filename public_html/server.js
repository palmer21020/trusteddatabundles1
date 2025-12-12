// Simple Express server to serve your PHP+HTML site on Render

const express = require("express");
const path = require("path");
const { exec } = require("child_process");

const app = express();

// Public folder (your HTML, PHP, API files)
const PUBLIC_DIR = path.join(__dirname, "public_html");

// Run PHP built-in server
const phpServer = exec(`php -S 0.0.0.0:9000 -t ${PUBLIC_DIR}`);

phpServer.stdout.on("data", data => console.log("PHP:", data));
phpServer.stderr.on("data", data => console.error("PHP ERROR:", data));

// Reverse-proxy requests to PHP server
app.use((req, res) => {
  const url = `http://127.0.0.1:9000${req.url}`;

  fetch(url)
    .then(response => response.text())
    .then(body => res.send(body))
    .catch(err => {
      console.error("Proxy error:", err);
      res.status(500).send("Internal server error");
    });
});

const PORT = process.env.PORT || 10000;

app.listen(PORT, () => {
  console.log(`Node server running on port ${PORT}`);
});
