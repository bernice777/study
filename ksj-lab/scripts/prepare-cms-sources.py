#!/usr/bin/env python3
"""Rebuild clean installer archives from the two original course archives."""
import argparse
import gzip
import hashlib
import io
from pathlib import Path, PurePosixPath
import tarfile

HASHES = {
    'xe': '2c93a92636464abd282de6c89e0961b5deadd97aef3468d4c29cb492d042ec29',
    'rb': 'e19e5eef1134de8eb4cfb1d87863d7b2ee631cfd24475f68d380ad9855698051',
}


def prepare(source, destination, app):
    raw = (source / (app + '.tgz')).read_bytes()
    if hashlib.sha256(raw).hexdigest() != HASHES[app]:
        raise ValueError('Unexpected source SHA-256: ' + app)
    entries = []
    with tarfile.open(fileobj=io.BytesIO(raw), mode='r:gz') as archive:
        for member in archive:
            path = PurePosixPath(member.name)
            if path.is_absolute() or '..' in path.parts or path.parts[0] != app:
                raise ValueError('Unsafe archive path: ' + member.name)
            if not (member.isfile() or member.isdir()):
                raise ValueError('Unexpected archive member: ' + member.name)
            relative = '/'.join(path.parts[1:])
            if member.isdir():
                entries.append((str(path), None))
                continue
            # Runtime state, credentials and generated PHP must not be distributed.
            excluded = ('files',) if app == 'xe' else (
                '_tmp', 'files', '_var/db.info.php', '_var/table.info.php',
                '_var/sitephp', '_var/menu', '_var/peak', '_var/xml',
                'modules/admin/var/users', 'wpi.var.php',
            )
            if any(relative == p or relative.startswith(p + '/') for p in excluded):
                continue
            data = archive.extractfile(member).read()
            if app == 'rb' and relative.endswith('/_setting/db.table.php.done'):
                relative = relative[:-5]
            if app == 'rb' and relative == 'modules/admin/lang.korean/action/a.install.php':
                # The historical default page embeds an external, obsolete website.
                lines = data.decode('utf-8').splitlines(keepends=True)
                data = ''.join(
                    "$maincontent = '<h2>KimsQ RB local lab</h2><p>KSJ web hacking practice</p>';\n"
                    if line.startswith('$maincontent = ') else line
                    for line in lines
                ).encode('utf-8')
            entries.append((app + '/' + relative, data))
    destination.mkdir(parents=True, exist_ok=True)
    target = destination / (app + '.tgz')
    with target.open('wb') as output, gzip.GzipFile(filename='', fileobj=output, mode='wb', mtime=0) as gz:
        with tarfile.open(fileobj=gz, mode='w') as archive:
            for name, data in sorted(entries):
                info = tarfile.TarInfo(name)
                if data is None:
                    info.type = tarfile.DIRTYPE
                    info.mode = 0o755
                    archive.addfile(info)
                    continue
                info.mode = 0o644
                info.size = len(data)
                archive.addfile(info, io.BytesIO(data))
    print(app, len(entries), 'entries', hashlib.sha256(target.read_bytes()).hexdigest())


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('source', type=Path, help='Directory containing original xe.tgz and rb.tgz')
    args = parser.parse_args()
    destination = Path(__file__).resolve().parents[1] / 'docker' / 'legacy' / 'archives'
    for app in HASHES:
        prepare(args.source, destination, app)
