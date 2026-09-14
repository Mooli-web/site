// Dev-only server (PHP in WebAssembly). Not needed on real hosting.
// usage: node serve.mjs <php-app dir> <port>
import { loadNodeRuntime, createNodeFsMountHandler } from '@php-wasm/node';
import { PHP } from '@php-wasm/universal';
import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
const root = process.argv[2];
const port = Number(process.argv[3] || 8080);
const docrootLocal = path.join(root, 'public');
const docrootWasm = '/www/public';
const MIME = { '.css': 'text/css', '.js': 'application/javascript', '.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.webp': 'image/webp', '.svg': 'image/svg+xml', '.woff2': 'font/woff2', '.woff': 'font/woff', '.ico': 'image/x-icon' };
let pid = 1;
async function makePhp() {
  const php = new PHP(await loadNodeRuntime('8.3', { emscriptenOptions: { processId: pid++ } }));
  php.mkdir('/www'); await php.mount('/www', createNodeFsMountHandler(root));
  return php;
}
let next = makePhp();
let queue = Promise.resolve();
http.createServer((req, res) => {
  const u = new URL(req.url, 'http://x');
  const local = path.join(docrootLocal, decodeURIComponent(u.pathname));
  if (u.pathname !== '/' && local.startsWith(docrootLocal) && fs.existsSync(local) && fs.statSync(local).isFile() && !local.endsWith('.php')) {
    res.writeHead(200, { 'content-type': MIME[path.extname(local)] || 'application/octet-stream' });
    fs.createReadStream(local).pipe(res); return;
  }
  queue = queue.then(async () => {
    const chunks = []; for await (const c of req) chunks.push(c);
    const body = Buffer.concat(chunks);
    console.log(new Date().toISOString(), req.method, req.url, 'cookie:', req.headers.cookie ? 'yes' : 'NO', 'origin:', req.headers.origin || '-', 'ua:', (req.headers['user-agent']||'').slice(0,40));
    const headers = {}; for (const [k, v] of Object.entries(req.headers)) headers[k] = Array.isArray(v) ? v.join(', ') : v;
    const php = await next; next = makePhp();
    try {
      const r = await php.run({
        scriptPath: docrootWasm + '/index.php', relativeUri: req.url, method: req.method, headers,
        body: body.length ? new Uint8Array(body) : undefined, protocol: 'http',
        $_SERVER: { HTTP_X_FORWARDED_PROTO: 'https', SERVER_NAME: 'localhost', SERVER_PORT: String(port), HTTP_HOST: headers.host || 'localhost', DOCUMENT_ROOT: docrootWasm, REQUEST_URI: req.url, SCRIPT_NAME: '/index.php', SCRIPT_FILENAME: docrootWasm + '/index.php' },
      });
      const h = {}; for (const [k, v] of Object.entries(r.headers)) h[k] = v;
      res.writeHead(r.httpStatusCode, h); res.end(Buffer.from(r.bytes));
    } catch (e) { res.writeHead(500); res.end(String(e.stack || e)); }
    try { php.exit(); } catch {}
  }).catch(e => { try { res.writeHead(500); res.end(String(e)); } catch {} });
}).listen(port, '0.0.0.0', () => console.log('listening', port));
