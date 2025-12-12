const express = require("express");
const path = require("path");
const app = express();

// Serve all files inside public_html
app.use(express.static(path.join(__dirname, "public_html")));

// Serve index.html as homepage
app.get("/", (req, res) => {
  res.sendFile(path.join(__dirname, "public_html", "index.html"));
});

// Example API route (for later use)
app.get("/api/test", (req, res) => {
  res.json({ message: "API is working!" });
});

// Render requires PORT to come from environment
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
