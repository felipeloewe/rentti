#!/usr/bin/env python3
"""Deploy RENTTI site + backoffice to Softnet cPanel (rentticl / public_html)."""
from __future__ import annotations

import json
import mimetypes
import os
import ssl
import sys
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
HOST = os.environ.get("RENTTI_CPANEL_HOST", "https://rentti.cl:2083")
USER = os.environ.get("RENTTI_CPANEL_USER", "rentticl")
TOKEN = os.environ.get("RENTTI_KEY", "")
DOCROOT = "public_html"

SKIP_NAMES = {
    ".git",
    ".gitignore",
    "README.md",
    "router.php",
    "scripts",
    "node_modules",
    ".DS_Store",
    "config.local.php",
    ".admin-pass.local",
}
SKIP_SUFFIXES = {".sqlite", ".sqlite-journal", ".sqlite-wal", ".sqlite-shm", ".log"}

CTX = ssl.create_default_context()


def api(path: str, query: dict | None = None, data: bytes | None = None, content_type: str | None = None) -> dict:
    if not TOKEN:
        raise SystemExit("RENTTI_KEY is not set")
    q = urllib.parse.urlencode(query or {})
    url = f"{HOST}{path}"
    if q:
        url += ("&" if "?" in url else "?") + q
    headers = {"Authorization": f"cpanel {USER}:{TOKEN}"}
    if content_type:
        headers["Content-Type"] = content_type
    req = urllib.request.Request(url, data=data, headers=headers, method="POST" if data is not None else "GET")
    with urllib.request.urlopen(req, context=CTX, timeout=120) as resp:
        body = resp.read().decode("utf-8", errors="replace")
    return json.loads(body)


def mkdir(dirpath: str) -> None:
    # Softnet: UAPI Fileman/mkdir often unavailable; use API2
    try:
        api("/json-api/cpanel", {
            "cpanel_jsonapi_user": USER,
            "cpanel_jsonapi_apiversion": "2",
            "cpanel_jsonapi_module": "Fileman",
            "cpanel_jsonapi_func": "mkdir",
            "path": str(Path(dirpath).parent).replace("\\", "/"),
            "name": Path(dirpath).name,
            "permissions": "0755",
        })
    except Exception as exc:  # noqa: BLE001
        print(f"mkdir note {dirpath}: {exc}")


def upload_file(local: Path, remote_dir: str) -> None:
    boundary = "----RenttiBoundary7MA4YWxkTrZu0gW"
    filename = local.name
    mime = mimetypes.guess_type(filename)[0] or "application/octet-stream"
    file_bytes = local.read_bytes()
    parts = []
    def add(name: str, value: str) -> None:
        parts.append(
            f"--{boundary}\r\n"
            f'Content-Disposition: form-data; name="{name}"\r\n\r\n'
            f"{value}\r\n".encode()
        )
    add("dir", remote_dir)
    add("overwrite", "1")
    parts.append(
        (
            f"--{boundary}\r\n"
            f'Content-Disposition: form-data; name="file-1"; filename="{filename}"\r\n'
            f"Content-Type: {mime}\r\n\r\n"
        ).encode()
        + file_bytes
        + b"\r\n"
    )
    parts.append(f"--{boundary}--\r\n".encode())
    body = b"".join(parts)
    result = api(
        "/execute/Fileman/upload_files",
        data=body,
        content_type=f"multipart/form-data; boundary={boundary}",
    )
    if not result.get("status"):
        raise RuntimeError(f"upload failed {local}: {result}")


def should_skip(path: Path) -> bool:
    rel = path.relative_to(ROOT)
    parts = set(rel.parts)
    if parts & SKIP_NAMES:
        return True
    if path.name in SKIP_NAMES:
        return True
    if path.suffix in SKIP_SUFFIXES:
        return True
    if path.name.startswith("."):
        return True
    return False


def collect_files() -> list[Path]:
    files: list[Path] = []
    for path in ROOT.rglob("*"):
        if not path.is_file():
            continue
        if should_skip(path):
            continue
        # skip local runtime under data/uploads except .htaccess and gitkeep
        rel = path.relative_to(ROOT).as_posix()
        if rel.startswith("data/") and path.name not in {".htaccess"}:
            continue
        if rel.startswith("uploads/properties/") and path.name not in {".gitkeep", ".htaccess"}:
            continue
        files.append(path)
    return sorted(files)


def ensure_dirs(files: list[Path]) -> None:
    dirs = {DOCROOT}
    for f in files:
        rel = f.relative_to(ROOT)
        parent = Path(DOCROOT) / rel.parent
        for i in range(len(parent.parts)):
            dirs.add(str(Path(*parent.parts[: i + 1])))
    # also ensure runtime dirs
    dirs.update({
        f"{DOCROOT}/data",
        f"{DOCROOT}/uploads",
        f"{DOCROOT}/uploads/properties",
        f"{DOCROOT}/config",
        f"{DOCROOT}/admin",
        f"{DOCROOT}/api",
        f"{DOCROOT}/lib",
    })
    for d in sorted(dirs, key=lambda s: s.count("/")):
        if d == DOCROOT:
            continue
        print(f"mkdir {d}")
        mkdir(d)


def write_remote_config() -> None:
    """Upload config.local.php from local untracked file if present."""
    local = ROOT / "config" / "config.local.php"
    if not local.is_file():
        print("WARN: no config.local.php — admin login will need env/config on server")
        return
    upload_file(local, f"{DOCROOT}/config")
    print("uploaded config/config.local.php")


def main() -> int:
    files = collect_files()
    print(f"deploying {len(files)} files to {USER}@{HOST}/{DOCROOT}")
    ensure_dirs(files)
    for f in files:
        rel = f.relative_to(ROOT)
        remote_dir = str(Path(DOCROOT) / rel.parent).replace("\\", "/")
        print(f"upload {rel}")
        upload_file(f, remote_dir)
    write_remote_config()
    print("done")
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except urllib.error.HTTPError as e:
        print("HTTPError", e.code, e.read()[:500], file=sys.stderr)
        raise
