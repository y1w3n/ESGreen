const express = require('express');
const mysql = require('mysql');
const cors = require('cors');

const app = express();
const port = 3000;

// Enable CORS
app.use(cors());

const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'esg_assessment'
});

db.connect(err => {
  if (err) {
    console.error('Database connection failed: ' + err.stack);
    return;
  }
  console.log('Connected to database.');
});

app.get('/data/:table', (req, res) => {
  const table = req.params.table;
  let query = '';
  switch (table) {
    case 'as_geninfo_pl':
      query = 'SELECT * FROM as_geninfo_pl';
      break;
    case 'hc_pl':
      query = 'SELECT * FROM hc_pl';
      break;
    case 'hc_tr_analysis_overall':
      query = 'SELECT * FROM hc_tr_analysis_overall';
      break;
    default:
      res.status(404).send('Table not found');
      return;
  }

  db.query(query, (err, results) => {
    if (err) {
      res.status(500).send(err);
      return;
    }
    res.json(results);
  });
});

app.listen(port, () => {
  console.log(`Server running at http://localhost:${port}/`);
});
