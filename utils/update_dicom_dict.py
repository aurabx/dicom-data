#!/usr/bin/env python3
import tempfile
import shutil
import subprocess
import json
import os
from pathlib import Path

REPO_URL = "https://github.com/innolitics/dicom-standard.git"
TARGET_BASE = Path("resources/dicom/innolitics")

# Files to exclude entirely from processing
EXCLUDE_FILES = {
    "references.json"
}

# Files to copy without rekeying
NO_KEY_REWRITE = {
    "ciod_to_func_group_macros.json",
    "ciod_to_modules.json",
    "macro_to_attributes.json",
    "module_to_attributes.json"
}

# Files that should be rekeyed with single/composite keys
KEY_CONFIG = {
    "attributes.json": "id",
    "modules.json": "id",
    "macros.json": "id",
    "ciods.json": "id",
    "confidentiality_profile_attributes.json": "id",
    "sops.json": "id"
}

EXCLUDE_FILES = {"references.json"}

def clone_repo():
    temp_dir = tempfile.mkdtemp(prefix="dicom-standard-")
    print(f"📥 Cloning repo to {temp_dir}")
    subprocess.run(["git", "clone", "--depth=1", REPO_URL, temp_dir], check=True)
    return Path(temp_dir)

def rekey_json_list(json_list, key_fields, file_name, separator="::"):
    rekeyed = {}

    for entry in json_list:
        if isinstance(key_fields, str):
            key = entry.get(key_fields)
            if key:
                key = key.upper()
        elif isinstance(key_fields, list):
            try:
                key = separator.join(str(entry[field]) for field in key_fields).upper()
            except KeyError as e:
                print(f"⚠️ Missing key component {e} in {file_name}, skipping entry.")
                continue
        else:
            print(f"❌ Invalid key config for {file_name}, expected str or list.")
            return {}

        if not key:
            print(f"⚠️ Skipping entry with missing key in {file_name}")
            continue

        if key in rekeyed:
            print(f"⚠️ Duplicate key '{key}' in {file_name}, overwriting.")

        rekeyed[key] = entry

    return rekeyed

def process_file(json_path: Path, target_json: Path, source_root: Path, php_root: Path):
    file_name = json_path.name

    if file_name in EXCLUDE_FILES:
        print(f"🚫 Excluding file completely: {file_name}")
        return

    with open(json_path, "r", encoding="utf-8") as f:
        data = json.load(f)

    target_json.parent.mkdir(parents=True, exist_ok=True)

    if file_name in NO_KEY_REWRITE:
        with open(target_json, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=2)

        write_php_array(data, json_path, source_root, php_root)

        print(f"➖ Copied and exported {file_name} without rekeying")
        return

    key_field = KEY_CONFIG.get(file_name)
    if not key_field:
        print(f"⚠️ No key config for {file_name}, skipping")
        return

    if not isinstance(data, list):
        print(f"⚠️ Unexpected format in {file_name}, expected list")
        return

    keyed_data = rekey_json_list(data, key_field, file_name)

    with open(target_json, "w", encoding="utf-8") as f:
        json.dump(keyed_data, f, indent=2)

    write_php_array(keyed_data, json_path, source_root, php_root)

    print(f"✅ Rewritten {file_name} with key '{key_field}' and exported to PHP")

def rewrite_all_jsons(source_root: Path, json_root: Path, php_root: Path):
    for json_path in source_root.rglob("*.json"):
        if json_path.name in EXCLUDE_FILES:
            print(f"🚫 Skipping excluded file: {json_path.name}")
            continue

        relative_path = json_path.relative_to(source_root)
        target_json = json_root / relative_path
        process_file(json_path, target_json, source_root, php_root)

def write_php_array(data: dict, source_path: Path, source_root: Path, php_root: Path):
    relative_path = source_path.relative_to(source_root).with_suffix(".php")
    php_path = php_root / relative_path
    php_path.parent.mkdir(parents=True, exist_ok=True)

    def to_php(val, indent=0):
        ind = ' ' * indent
        if isinstance(val, dict):
            out = "[\n"
            for k, v in val.items():
                out += f"{ind}    {repr(k)} => {to_php(v, indent + 4)},\n"
            out += f"{ind}]"
            return out
        elif isinstance(val, list):
            out = "[\n"
            for v in val:
                out += f"{ind}    {to_php(v, indent + 4)},\n"
            out += f"{ind}]"
            return out
        elif isinstance(val, bool):
            return 'true' if val else 'false'
        elif val is None:
            return 'null'
        else:
            return repr(val)

    php_array = "<?php\n\nreturn " + to_php(data, 0) + ";\n"
    with open(php_path, "w", encoding="utf-8") as f:
        f.write(php_array)

    print(f"📝 Exported PHP array to {php_path}")

def main():
    repo_path = clone_repo()
    try:
        json_output = TARGET_BASE
        php_output = Path("resources/dicom/php")
        rewrite_all_jsons(repo_path, json_output, php_output)
    finally:
        print(f"🧹 Cleaning up {repo_path}")
        shutil.rmtree(repo_path)

if __name__ == "__main__":
    main()
