#!/usr/bin/env python3
"""Generate the Joomla update feed from a published, downloaded release ZIP."""
import argparse
import hashlib
from pathlib import Path
import re
import xml.etree.ElementTree as ET

REPO = "Thidjs/scouting-nederland-oidc"
BASE = Path(__file__).resolve().parent.parent

def main():
    p = argparse.ArgumentParser()
    p.add_argument("--version", required=True)
    p.add_argument("--zip", required=True, type=Path)
    p.add_argument("--output", required=True, type=Path)
    args = p.parse_args()
    if not re.fullmatch(r"[0-9]+\.[0-9]+\.[0-9]+", args.version):
        p.error("version must be X.Y.Z")
    if args.zip.name != f"pkg_scoutingoidc-{args.version}.zip" or not args.zip.is_file():
        p.error("ZIP missing or name does not match version")
    digest = hashlib.sha256(args.zip.read_bytes()).hexdigest()
    root = ET.parse(BASE / "docs/updates/scoutingoidc.xml").getroot()
    update = root.find("update")
    if update is None or update.findtext("element") != "pkg_scoutingoidc" or update.findtext("type") != "package":
        p.error("invalid package update template")
    update.find("version").text = args.version
    update.find("downloads/downloadurl").text = (
        f"https://github.com/{REPO}/releases/download/v{args.version}/"
        f"pkg_scoutingoidc-{args.version}.zip"
    )
    update.find("sha256").text = digest
    ET.indent(root, space="    ")
    args.output.parent.mkdir(parents=True, exist_ok=True)
    ET.ElementTree(root).write(args.output, encoding="utf-8", xml_declaration=True)
    print(f"Generated {args.output}: v{args.version} sha256={digest}")

if __name__ == "__main__":
    main()
