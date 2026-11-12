
import firebase_admin
from firebase_admin import credentials, firestore
from google.cloud.firestore_v1 import FieldFilter
from pathlib import Path
from collections import defaultdict
import sys

sys.stdout.reconfigure(encoding="utf-8")

# Cau hinh
CREDENTIAL_PATH = Path(
    r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"
)
LOCAL_ROOT = Path(r"D:\Projects\HCSDLDPT\source")

# Anh xa ten thu muc local sang storagePath tren Firestore
FOLDER_MAPPING = {
    "Ă": "A_BREVE",
    "Â": "A_CIRCUMFLEX",
    "Đ": "D_STROKE",
    "Ê": "E_CIRCUMFLEX",
    "Ô": "O_CIRCUMFLEX",
    "Ơ": "O_HORN",
    "Ư": "U_HORN",
}

BATCH_SIZE = 30

# Ket noi Firestore
cred = credentials.Certificate(str(CREDENTIAL_PATH))

if not firebase_admin._apps:
    firebase_admin.initialize_app(cred)

db = firestore.client()
collection = db.collection("words")


def get_storage_path(file_path):
    relative = file_path.relative_to(LOCAL_ROOT)
    parts = list(relative.parts)

    if parts:
        parts[0] = FOLDER_MAPPING.get(
            parts[0].upper(), parts[0]
        )

    return "/".join(parts)


print("\n===== QUET FILE WAV LOCAL =====")

wav_files = list(LOCAL_ROOT.rglob("*.wav"))

local_paths = {}

for file_path in wav_files:
    storage_path = get_storage_path(file_path)
    local_paths[storage_path] = file_path

print("Tong so file WAV:", len(wav_files))
print("So storagePath duy nhat:", len(local_paths))


# Truy van Firestore theo tung nhom 30 storagePath
all_paths = list(local_paths.keys())
firestore_records = defaultdict(list)

print("\n===== DOI CHIEU FIRESTORE =====")

total_batches = (len(all_paths) + BATCH_SIZE - 1) // BATCH_SIZE

for index in range(0, len(all_paths), BATCH_SIZE):
    batch = all_paths[index:index + BATCH_SIZE]

    query = collection.where(
        filter=FieldFilter("storagePath", "in", batch)
    )

    for doc in query.stream():
        data = doc.to_dict()
        path = data.get("storagePath")

        if path:
            firestore_records[path].append({
                "id": doc.id,
                "word": data.get("word"),
                "fileName": data.get("fileName"),
            })

    current_batch = index // BATCH_SIZE + 1
    print(f"Da kiem tra nhom {current_batch}/{total_batches}")


# Tong hop ket qua
missing_metadata = []
duplicate_metadata = []

for path in all_paths:
    records = firestore_records.get(path, [])

    if len(records) == 0:
        missing_metadata.append(path)

    elif len(records) > 1:
        duplicate_metadata.append((path, records))


print("\n===== KET QUA DOI CHIEU =====")
print("Tong WAV local:", len(wav_files))
print("Co metadata:", len(local_paths) - len(missing_metadata))
print("Thieu metadata:", len(missing_metadata))
print("Trung storagePath:", len(duplicate_metadata))


# Xuat danh sach WAV chua co metadata
report_path = Path("wav_missing_metadata.txt")

with report_path.open("w", encoding="utf-8") as f:
    for path in missing_metadata:
        f.write(path + "\n")

print("\n===== DANH SACH THIEU METADATA =====")

for path in missing_metadata:
    print(path)

if not missing_metadata:
    print("Khong co file WAV nao bi thieu metadata.")

if duplicate_metadata:
    print("\n===== STORAGEPATH BI TRUNG =====")
    for path, records in duplicate_metadata:
        print(path, records)

print("\nDa xuat bao cao:", report_path.resolve())
print("===== HOAN TAT =====")
