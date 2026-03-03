import express from 'express';
import { createServer as createViteServer } from 'vite';
import Database from 'better-sqlite3';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function startServer() {
  const app = express();
  const PORT = 3000;
  const db = new Database('meal.db');

  // Initialize Database
  db.exec(`
    CREATE TABLE IF NOT EXISTS indicators (
      id TEXT PRIMARY KEY,
      name TEXT NOT NULL,
      target REAL NOT NULL,
      actual REAL DEFAULT 0,
      unit TEXT,
      category TEXT,
      trend TEXT DEFAULT 'stable',
      status TEXT DEFAULT 'on-track',
      gap REAL DEFAULT 0,
      achievedPercentage REAL DEFAULT 0,
      lastUpdated TEXT DEFAULT CURRENT_TIMESTAMP,
      geojson TEXT
    );

    CREATE TABLE IF NOT EXISTS monitoring_entries (
      id TEXT PRIMARY KEY,
      indicatorId TEXT,
      date TEXT NOT NULL,
      value REAL NOT NULL,
      location TEXT,
      notes TEXT,
      latitude REAL,
      longitude REAL,
      FOREIGN KEY (indicatorId) REFERENCES indicators(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS qualitative_feedback (
      id TEXT PRIMARY KEY,
      date TEXT NOT NULL,
      source TEXT,
      content TEXT NOT NULL,
      sentiment TEXT,
      themes TEXT,
      summary TEXT
    );
  `);

  // Seed initial data if empty
  const count = db.prepare('SELECT count(*) as count FROM indicators').get() as { count: number };
  if (count.count === 0) {
    const insert = db.prepare(`
      INSERT INTO indicators (id, name, target, actual, unit, category, trend, status, gap, achievedPercentage) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);
    insert.run('1', 'Number of beneficiaries reached', 5000, 4250, 'people', 'Outreach', 'up', 'on-track', 750, 85);
    insert.run('2', 'Training completion rate', 95, 88, '%', 'Capacity Building', 'stable', 'at-risk', 7, 92.6);
    insert.run('3', 'Community satisfaction index', 4.5, 4.2, '/5', 'Accountability', 'up', 'on-track', 0.3, 93.3);
    insert.run('4', 'Average response time to feedback', 48, 36, 'hours', 'Accountability', 'down', 'on-track', -12, 125);
  }

  app.use(express.json());

  // API Routes
  app.get('/api/indicators', (req, res) => {
    const indicators = db.prepare('SELECT * FROM indicators').all();
    res.json(indicators.map((ind: any) => ({
      ...ind,
      geojson: ind.geojson ? JSON.parse(ind.geojson) : null
    })));
  });

  app.post('/api/monitoring', (req, res) => {
    const { id, indicatorId, date, value, location, notes, latitude, longitude } = req.body;
    
    const insert = db.prepare(`
      INSERT INTO monitoring_entries (id, indicatorId, date, value, location, notes, latitude, longitude)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `);
    
    const updateIndicator = db.prepare(`
      UPDATE indicators 
      SET actual = actual + ?, 
          lastUpdated = CURRENT_TIMESTAMP
      WHERE id = ?
    `);

    const transaction = db.transaction(() => {
      insert.run(id, indicatorId, date, value, location, notes, latitude, longitude);
      updateIndicator.run(value, indicatorId);
      
      // Recalculate achievement and status
      const ind = db.prepare('SELECT * FROM indicators WHERE id = ?').get() as any;
      const newAchieved = Math.round((ind.actual / ind.target) * 100);
      const newGap = ind.target - ind.actual;
      const newStatus = newAchieved >= 90 ? 'on-track' : newAchieved >= 70 ? 'at-risk' : 'behind';
      
      db.prepare('UPDATE indicators SET achievedPercentage = ?, gap = ?, status = ? WHERE id = ?')
        .run(newAchieved, newGap, newStatus, indicatorId);
    });

    transaction();
    res.json({ status: 'success' });
  });

  app.get('/api/monitoring', (req, res) => {
    const entries = db.prepare('SELECT * FROM monitoring_entries').all();
    res.json(entries.map((e: any) => ({
      ...e,
      coordinates: e.latitude && e.longitude ? [e.latitude, e.longitude] : null
    })));
  });

  app.post('/api/feedback', (req, res) => {
    const { id, date, source, content, sentiment, themes, summary } = req.body;
    const insert = db.prepare(`
      INSERT INTO qualitative_feedback (id, date, source, content, sentiment, themes, summary)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `);
    insert.run(id, date, source, content, sentiment, JSON.stringify(themes), summary);
    res.json({ status: 'success' });
  });

  app.get('/api/feedback', (req, res) => {
    const feedback = db.prepare('SELECT * FROM qualitative_feedback').all();
    res.json(feedback.map((f: any) => ({
      ...f,
      themes: JSON.parse(f.themes || '[]')
    })));
  });

  // Vite middleware for development
  if (process.env.NODE_ENV !== 'production') {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: 'spa',
    });
    app.use(vite.middlewares);
  } else {
    app.use(express.static(path.join(__dirname, 'dist')));
    app.get('*', (req, res) => {
      res.sendFile(path.join(__dirname, 'dist', 'index.html'));
    });
  }

  app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}

startServer();
