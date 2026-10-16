#!/usr/bin/env python3
"""Build a reproducible, installable Joomla zip from a plugin source folder.

Usage:
    python build/build_plugin.py plugins/plg_system_yooarchive [--out build/out]

- Reads type, group, element and version from the manifest (the only *.xml in the
  folder root whose root tag is <extension>).
- Writes <out>/plg_<group>_<element>-<version>.zip with forward-slash paths,
  files at the zip root, sorted entries and a fixed timestamp, so the same source
  always gives the same bytes (same sha256).
- Prints the sha256 to paste into updates/<element>.xml.

Never use PowerShell Compress-Archive for Joomla packages: it writes backslashes.
"""

import argparse
import hashlib
import sys
import xml.etree.ElementTree as ET
import zipfile
from pathlib import Path

FIXED_DATE = (2026, 1, 1, 0, 0, 0)
EXCLUDE_NAMES = {".DS_Store", "Thumbs.db", ".gitkeep"}
EXCLUDE_DIRS = {".git", "__pycache__", "node_modules", "tests"}


def find_manifest(src: Path) -> Path:
    for xml in sorted(src.glob("*.xml")):
        try:
            if ET.parse(xml).getroot().tag == "extension":
                return xml
        except ET.ParseError:
            continue
    sys.exit(f"No Joomla manifest found in {src}")


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("src")
    parser.add_argument("--out", default=str(Path(__file__).resolve().parent / "out"))
    args = parser.parse_args()

    src = Path(args.src).resolve()
    manifest = find_manifest(src)
    root = ET.parse(manifest).getroot()
    ext_type = root.get("type")
    version = (root.findtext("version") or "").strip()
    if not version:
        sys.exit("Manifest without <version>")

    if ext_type == "plugin":
        group = root.get("group")
        element = manifest.stem
        name = f"plg_{group}_{element}-{version}.zip"
    else:
        name = f"{manifest.stem}-{version}.zip"

    files = []
    for path in sorted(src.rglob("*")):
        rel = path.relative_to(src)
        if path.is_dir() or path.name in EXCLUDE_NAMES or any(p in EXCLUDE_DIRS for p in rel.parts):
            continue
        files.append((rel.as_posix(), path))

    out = Path(args.out)
    out.mkdir(parents=True, exist_ok=True)
    target = out / name

    with zipfile.ZipFile(target, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as zf:
        for arcname, path in files:
            info = zipfile.ZipInfo(arcname, date_time=FIXED_DATE)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o644 << 16
            info.create_system = 3
            zf.writestr(info, path.read_bytes())

    with zipfile.ZipFile(target) as zf:
        bad = [n for n in zf.namelist() if "\\" in n]
        if bad:
            sys.exit(f"Backslash in zip entries: {bad}")

    sha = hashlib.sha256(target.read_bytes()).hexdigest()
    print(f"{target}\n{len(files)} files\nsha256 {sha}")


if __name__ == "__main__":
    main()
