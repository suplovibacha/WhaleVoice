import csv
import os
import requests
from pathlib import Path
from collections import Counter

# ==============================
# CAU HINH
# ==============================

PROJECT_ROOT = Path(__file__).resolve().parent
SOURCE_DIR = Path(r"D:\Projects\HCSDLDPT\source")
REPORT_DIR = PROJECT_ROOT / "storage" / "app" / "audit_reports"

BUCKET = "whalespeed-audio"
BASE_URL = "https://dgofnbnqpiwxqqupsxbx.supabase.co"

LIMIT = 1000

FOLDER_MAPPING = {
    "Ă": "A_BREVE",
    "Â": "A_CIRCUMFLEX",
    "Đ": "D_STROKE",
    "Ê": "E_CIRCUMFLEX",
    "Ô": "O_CIRCUMFLEX",
    "Ơ": "O_HORN",
    "Ư": "U_HORN",
}

# ==============================
# DOC SERVICE ROLE KEY TU .ENV
# ==============================

def read_env_value(key):
    env_file = PROJECT_ROOT / ".env"

    with env_file.open("r", encoding="utf-8-sig") as f:
        for line in f:
            line = line.strip()

            if line.startswith(key + "="):
                value = line.split("=", 1)[1].strip()
                return value.strip('"').strip("'")

    return None


SERVICE_ROLE_KEY = read_env_value("SUPABASE_SERVICE_ROLE_KEY")

if not SERVICE_ROLE_KEY:
    raise RuntimeError(
        "Khong tim thay SUPABASE_SERVICE_ROLE_KEY trong file .env"
    )

HEADERS = {
    "apikey": SERVICE_ROLE_KEY,
    "Authorization": f"Bearer {SERVICE_ROLE_KEY}",
    "Content-Type": "application/json",
}

LIST_URL = f"{BASE_URL}/storage/v1/object/list/{BUCKET}"

# ==============================
# LIET KE TOAN BO OBJECT SUPABASE
# ==============================

def list_folder(prefix=""):
    objects = []
    offset = 0

    while True:
        payload = {
            "prefix": prefix,
            "limit": LIMIT,
            "offset": offset,
            "sortBy": {
                "column": "name",
                "order": "asc"
            }
        }

        response = requests.post(
            LIST_URL,
            headers=HEADERS,
            json=payload,
            timeout=60
        )

        if response.status_code != 200:
            raise RuntimeError(
                f"Loi liet ke Supabase: HTTP {response.status_code}\n"
                f"{response.text[:1000]}"
            )

        batch = response.json()

        if not isinstance(batch, list):
            raise RuntimeError(
                f"Du lieu Supabase khong hop le tai prefix: {prefix}"
            )

        objects.extend(batch)

        print(
            f"  Prefix [{prefix or 'ROOT'}]: "
            f"da lay {len(objects)} muc"
        )

        if len(batch) < LIMIT:
            break

        offset += LIMIT

    return objects


def collect_all_objects():
    all_paths = set()
    visited_folders = set()
    folders_to_scan = [""]

    while folders_to_scan:
        prefix = folders_to_scan.pop(0)

        if prefix in visited_folders:
            continue

        visited_folders.add(prefix)

        items = list_folder(prefix)

        for item in items:
            name = item.get("name")

            if not name:
                continue

            full_path = prefix + name

            # Folder trong Storage thuong co id = None.
            if item.get("id") is None:
                folder_prefix = full_path.rstrip("/") + "/"

                if folder_prefix not in visited_folders:
                    folders_to_scan.append(folder_prefix)
            else:
                all_paths.add(full_path)

    return all_paths


# ==============================
# TAO CAC DUONG DAN STORAGE CO THE
# ==============================

def get_candidate_paths(local_file):
    relative = local_file.relative_to(SOURCE_DIR)
    folder = relative.parts[0]
    filename = local_file.name

    # Thu muc D co the chua tu D hoac tu bat dau bang D-stroke.
    # Kiem tra ca hai vi tri, khong tu y ket luan.
    if folder == "D":
        return [
            f"D/{filename}",
            f"D_STROKE/{filename}"
        ]

    storage_folder = FOLDER_MAPPING.get(folder, folder)

    return [f"{storage_folder}/{filename}"]


# ==============================
# DOI CHIEU SOURCE VA SUPABASE
# ==============================

def main():
    REPORT_DIR.mkdir(parents=True, exist_ok=True)

    if not SOURCE_DIR.exists():
        raise RuntimeError(f"Khong tim thay thu muc: {SOURCE_DIR}")

    local_files = sorted(
        SOURCE_DIR.rglob("*"),
        key=lambda p: str(p).lower()
    )

    local_files = [
        p for p in local_files
        if p.is_file() and p.suffix.lower() == ".wav"
    ]

    print("\n===== THONG KE SOURCE =====")
    print(f"Tong WAV local: {len(local_files)}")

    print("\n===== LAY DANH SACH SUPABASE =====")

    remote_paths = collect_all_objects()

    print(f"\nTong object Supabase: {len(remote_paths)}")

    results = []

    for index, local_file in enumerate(local_files, 1):
        candidates = get_candidate_paths(local_file)

        matched = [
            path for path in candidates
            if path in remote_paths
        ]

        if len(matched) == 1:
            status = "FOUND"
            remote_path = matched[0]

        elif len(matched) > 1:
            status = "FOUND_MULTIPLE"
            remote_path = ";".join(matched)

        elif local_file.parent.name == "D":
            status = "MISSING_D_NEEDS_CLASSIFICATION"
            remote_path = ""

        else:
            status = "MISSING"
            remote_path = ""

        results.append({
            "local_path": str(local_file),
            "local_folder": local_file.parent.name,
            "fileName": local_file.name,
            "candidate_storage_paths": ";".join(candidates),
            "remote_storage_path": remote_path,
            "status": status,
        })

        if index % 200 == 0 or index == len(local_files):
            print(f"Doi chieu: {index}/{len(local_files)}")

    # Bao cao doi chieu
    audit_file = REPORT_DIR / "source_supabase_audit.csv"

    with audit_file.open(
        "w",
        encoding="utf-8-sig",
        newline=""
    ) as f:
        writer = csv.DictWriter(
            f,
            fieldnames=results[0].keys()
        )
        writer.writeheader()
        writer.writerows(results)

    # Danh sach object co tren Supabase nhung khong thay trong source
    local_candidate_paths = set()

    for local_file in local_files:
        local_candidate_paths.update(
            get_candidate_paths(local_file)
        )

    extra_remote = sorted(
        remote_paths - local_candidate_paths
    )

    extra_file = REPORT_DIR / "supabase_objects_not_in_source.csv"

    with extra_file.open(
        "w",
        encoding="utf-8-sig",
        newline=""
    ) as f:
        writer = csv.writer(f)
        writer.writerow(["storagePath"])

        for path in extra_remote:
            writer.writerow([path])

    # Tong ket
    counts = Counter(row["status"] for row in results)

    print("\n===== KET QUA DOI CHIEU =====")

    for status, count in counts.items():
        print(f"{status}: {count}")

    print("\n===== BAO CAO =====")
    print(f"File doi chieu: {audit_file}")
    print(f"Object khong thay trong source: {extra_file}")

    print("\nCHUA UPLOAD BAT KY FILE NAO.")


if __name__ == "__main__":
    main()
