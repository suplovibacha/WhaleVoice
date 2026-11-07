
import argparse
import csv
import os
import time
from pathlib import Path
from urllib.parse import quote

import requests


ROOT = Path(__file__).resolve().parent
SOURCE_DIR = Path(r"D:\Projects\HCSDLDPT\source")
REPORT_DIR = ROOT / "storage" / "app" / "audit_reports"

AUDIT_FILE = REPORT_DIR / "source_supabase_audit.csv"
OUTPUT_FILE = REPORT_DIR / "missing_upload_results.csv"

BUCKET = "whalespeed-audio"
BASE_URL = "https://dgofnbnqpiwxqqupsxbx.supabase.co"
LIMIT = 1000


def read_env(key):
    env_file = ROOT / ".env"

    with env_file.open("r", encoding="utf-8-sig") as f:
        for line in f:
            line = line.strip()

            if line.startswith(key + "="):
                return line.split("=", 1)[1].strip().strip('"').strip("'")

    return None


SERVICE_KEY = read_env("SUPABASE_SERVICE_ROLE_KEY")

if not SERVICE_KEY:
    raise RuntimeError("Khong tim thay SUPABASE_SERVICE_ROLE_KEY")

HEADERS = {
    "apikey": SERVICE_KEY,
    "Authorization": f"Bearer {SERVICE_KEY}",
}

LIST_URL = f"{BASE_URL}/storage/v1/object/list/{BUCKET}"
UPLOAD_URL = f"{BASE_URL}/storage/v1/object/{BUCKET}"


def list_remote(prefix=""):
    result = []
    offset = 0

    while True:
        response = requests.post(
            LIST_URL,
            headers={
                **HEADERS,
                "Content-Type": "application/json",
            },
            json={
                "prefix": prefix,
                "limit": LIMIT,
                "offset": offset,
                "sortBy": {
                    "column": "name",
                    "order": "asc",
                },
            },
            timeout=60,
        )

        response.raise_for_status()
        batch = response.json()

        if not isinstance(batch, list):
            raise RuntimeError(f"Du lieu khong hop le: {prefix}")

        result.extend(batch)

        if len(batch) < LIMIT:
            break

        offset += LIMIT

    return result


def collect_remote_paths():
    paths = set()
    scanned = set()
    queue = [""]

    while queue:
        prefix = queue.pop(0)

        if prefix in scanned:
            continue

        scanned.add(prefix)

        for item in list_remote(prefix):
            name = item.get("name")

            if not name:
                continue

            full_path = prefix + name

            if item.get("id") is None:
                queue.append(full_path.rstrip("/") + "/")
            else:
                paths.add(full_path)

    return paths


def encoded_path(storage_path):
    return "/".join(
        quote(part, safe="")
        for part in storage_path.split("/")
    )


def upload_one(local_path, storage_path):
    url = f"{UPLOAD_URL}/{encoded_path(storage_path)}"

    with local_path.open("rb") as audio:
        response = requests.post(
            url,
            headers={
                **HEADERS,
                "Content-Type": "audio/wav",
                "x-upsert": "false",
            },
            data=audio,
            timeout=180,
        )

    if response.status_code in (200, 201):
        return "UPLOADED", ""

    if response.status_code == 409:
        return "SKIPPED_EXISTS", response.text[:500]

    return f"ERROR_{response.status_code}", response.text[:1000]


def save_result(row, write_header=False):
    fields = [
        "local_folder",
        "fileName",
        "storagePath",
        "local_path",
        "status",
        "detail",
    ]

    mode = "w" if write_header else "a"

    with OUTPUT_FILE.open(
        mode,
        encoding="utf-8-sig" if write_header else "utf-8",
        newline="",
    ) as f:
        writer = csv.DictWriter(f, fieldnames=fields)

        if write_header:
            writer.writeheader()

        writer.writerow(row)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--execute",
        action="store_true",
        help="Thuc hien upload that",
    )
    args = parser.parse_args()

    if not AUDIT_FILE.exists():
        raise RuntimeError(f"Khong tim thay: {AUDIT_FILE}")

    with AUDIT_FILE.open(
        "r",
        encoding="utf-8-sig",
        newline="",
    ) as f:
        rows = [
            row for row in csv.DictReader(f)
            if row["status"] == "MISSING"
        ]

    if not rows:
        print("Khong co file MISSING nao.")
        return

    print(f"Tong file trong danh sach: {len(rows)}")

    print("\nDang kiem tra lai Supabase...")
    remote_paths = collect_remote_paths()

    print(f"So object hien co: {len(remote_paths)}")

    tasks = []

    for row in rows:
        local_path = Path(row["local_path"])
        storage_path = row["candidate_storage_paths"]

        if not local_path.is_file():
            print(f"LOCAL_NOT_FOUND: {local_path}")
            continue

        if storage_path in remote_paths:
            continue

        tasks.append({
            "local_folder": row["local_folder"],
            "fileName": row["fileName"],
            "storagePath": storage_path,
            "local_path": str(local_path),
        })

    print(f"So file can upload: {len(tasks)}")
    print(f"So file da co tren Supabase: {len(rows) - len(tasks)}")

    if not args.execute:
        print("\nCHE DO KIEM TRA: CHUA UPLOAD FILE NAO.")
        print("Neu so luong hop ly, chay lai voi --execute.")
        return

    if OUTPUT_FILE.exists():
        backup = OUTPUT_FILE.with_name(
            "missing_upload_results_previous.csv"
        )
        OUTPUT_FILE.replace(backup)
        print(f"Da sao luu bao cao cu: {backup}")

    counts = {}

    print("\n===== BAT DAU UPLOAD =====")

    for index, task in enumerate(tasks, 1):
        try:
            # Kiem tra lai file tren may
            local_path = Path(task["local_path"])

            if not local_path.is_file():
                status, detail = "LOCAL_NOT_FOUND", ""
            else:
                status, detail = upload_one(
                    local_path,
                    task["storagePath"],
                )

                if status == "UPLOADED":
                    remote_paths.add(task["storagePath"])

        except requests.RequestException as error:
            status = "CONNECTION_ERROR"
            detail = str(error)[:1000]

        except Exception as error:
            status = "ERROR"
            detail = str(error)[:1000]

        result = {
            **task,
            "status": status,
            "detail": detail,
        }

        save_result(result, write_header=(index == 1))

        counts[status] = counts.get(status, 0) + 1

        print(
            f"{index}/{len(tasks)}: "
            f"{task['storagePath']} - {status}"
        )

        time.sleep(0.05)

    print("\n===== KET QUA UPLOAD =====")

    for status, count in counts.items():
        print(f"{status}: {count}")

    print(f"\nBao cao: {OUTPUT_FILE}")
    print("Khong tao hoac sua tai lieu Firestore.")


if __name__ == "__main__":
    main()
