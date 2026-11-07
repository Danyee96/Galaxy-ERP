# Isolated deployment regression: never connects to the production database.
import pathlib,tempfile,subprocess,shutil,sqlite3,base64,os,hashlib,json
root=pathlib.Path(__file__).resolve().parents[2];base=pathlib.Path(tempfile.mkdtemp(prefix='galaxy-deploy-twice-'));source=base/'checkout';domain=base/'domain';app=domain/'laravel_app';web=domain/'public_html';source.mkdir();app.mkdir(parents=True);web.mkdir()
names=subprocess.check_output(['git','ls-files','--cached','--others','--exclude-standard'],cwd=root,text=True).splitlines()
for name in names:
 p=root/name
 if not p.is_file():continue
 q=source/name;q.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(p,q)
shutil.copytree(source/'app',app/'app')
shutil.copytree(root/'vendor',app/'vendor')
(app/'storage').mkdir();db=app/'storage/database.sqlite';c=sqlite3.connect(db);c.execute('CREATE TABLE preservation_probe (id INTEGER PRIMARY KEY,value TEXT)');c.execute("INSERT INTO preservation_probe VALUES(1,'must-not-change')");c.commit();c.close()
env='APP_NAME=Galaxy\nAPP_ENV=production\nAPP_DEBUG=false\nWAREHOUSES_ENABLED=false\nAPP_KEY=base64:'+base64.b64encode(os.urandom(32)).decode()+'\nAPP_URL=http://127.0.0.1:18768\nDB_CONNECTION=sqlite\nDB_DATABASE='+str(db)+'\nSESSION_DRIVER=file\nCACHE_DRIVER=file\n'
(app/'.env').write_text(env);os.chmod(app/'.env',0o600);(app/'storage/keep.txt').write_text('storage preserved')
(web/'uploads').mkdir();(web/'uploads/keep.txt').write_text('public upload preserved');(web/'app').mkdir();(web/'app/private.php').write_text('<?php // old private code');(web/'.env').write_text('fake old misplaced configuration');(web/'storage/logs').mkdir(parents=True);(web/'storage/logs/private.log').write_text('private log')
envhash=hashlib.sha256((app/'.env').read_bytes()).hexdigest();dbhash=hashlib.sha256(db.read_bytes()).hexdigest()
for n in [1,2]:
 r=subprocess.run(['bash',str(source/'scripts/deploy-hostinger.sh'),str(domain)],cwd=source,env=dict(os.environ,PHP_BIN=shutil.which('php'),COMPOSER_BIN=shutil.which('composer')),capture_output=True,text=True)
 (base/f'deploy-{n}.log').write_text(r.stdout+r.stderr)
 if r.returncode:
  print('FAIL deployment',n,'log:',base/f'deploy-{n}.log');print((r.stdout+r.stderr)[-2800:]);raise SystemExit(r.returncode)
 assert (app/'.env').read_text()==env
 assert hashlib.sha256(db.read_bytes()).hexdigest()==dbhash
 assert (app/'storage/keep.txt').read_text()=='storage preserved'
 assert (web/'uploads/keep.txt').read_text()=='public upload preserved'
 assert (app/'public/uploads/keep.txt').read_text()=='public upload preserved'
 assert not (web/'app').exists() and not (web/'.env').exists()
 assert not (web/'storage/logs').exists()
 print('PASS deployment',n,'layout, .env/APP_KEY, SQLite database, private storage and public uploads preserved.')
# Explicit activation is allowed only for an initialized ledger and survives deployment.
active_env=env.replace('WAREHOUSES_ENABLED=false','WAREHOUSES_ENABLED=true')
(app/'.env').write_text(active_env)
r=subprocess.run(['php',str(source/'scripts/validate-production-env.php'),str(app)],capture_output=True,text=True)
assert r.returncode!=0,'An uninitialized warehouse ledger was accepted'
c=sqlite3.connect(db);c.execute('CREATE TABLE warehouse_state (key TEXT PRIMARY KEY,value TEXT)');c.execute("INSERT INTO warehouse_state VALUES('ready','1')");c.commit();c.close()
active_dbhash=hashlib.sha256(db.read_bytes()).hexdigest()
r=subprocess.run(['bash',str(source/'scripts/deploy-hostinger.sh'),str(domain)],cwd=source,env=dict(os.environ,PHP_BIN=shutil.which('php'),COMPOSER_BIN=shutil.which('composer')),capture_output=True,text=True)
(base/'deploy-active.log').write_text(r.stdout+r.stderr)
if r.returncode:print((r.stdout+r.stderr)[-2000:]);raise SystemExit(r.returncode)
assert (app/'.env').read_text()==active_env
assert hashlib.sha256(db.read_bytes()).hexdigest()==active_dbhash
print('PASS initialized warehouse activation preserved across deployment; uninitialized activation rejected.')
(base/'fixture.json').write_text(json.dumps({'domain':str(domain),'source':str(source)}));pathlib.Path('/tmp/galaxy-deploy-fixture-path').write_text(str(base));print('Fixture:',base)
