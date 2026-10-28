#!/usr/bin/env python3
"""Build a Joomla package ZIP from this source tree.
Note: ZIP metadata differs from the original v1.0.0 artifact.
"""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
from io import BytesIO
ROOT = Path(__file__).resolve().parent.parent
PACKAGE_FILES = ["LICENSE", "NOTICE.md", "pkg_scoutingoidc.xml", "pkg_script.php"]
PLUGINS = {
    "packages/plg_system_scoutingoidc.zip": "plugins/system/scoutingoidc",
    "packages/plg_user_scoutingoidcsecurity.zip": "plugins/user/scoutingoidcsecurity",
}

def inner_archive(folder):
    data = BytesIO()
    with ZipFile(data, "w", ZIP_DEFLATED) as z:
        for p in sorted((ROOT / folder).rglob("*")):
            if p.is_file():
                z.write(p, p.relative_to(ROOT / folder).as_posix())
    return data.getvalue()

if __name__ == "__main__":
    target = ROOT / "dist" / "pkg_scoutingoidc-build.zip"
    target.parent.mkdir(parents=True, exist_ok=True)
    with ZipFile(target, "w", ZIP_DEFLATED) as z:
        for filename in PACKAGE_FILES:
            z.write(ROOT / filename, filename)
        for archive, folder in PLUGINS.items():
            z.writestr(archive, inner_archive(folder))
    print(target)
