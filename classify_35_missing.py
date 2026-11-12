import firebase_admin
from firebase_admin import credentials, firestore
from pathlib import Path
from collections import defaultdict

CREDENTIAL_PATH = Path(
    r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"
)

MISSING_FILE = Path("wav_missing_metadata.txt")

cred = credentials.Certificate(str(CREDENTIAL_PATH))

if not firebase_admin._apps:
    firebase_admin.initialize_app(cred)

db = firestore.client()
collection = db.collection("words")


def normalize(value):
    return str(value or "").strip().casefold()


def remove_variant_suffix(filename):
    name = Path(filename).stem
    suffix = Path(filename).suffix

    if name.endswith("-2"):
        name = name[:-2]

    return normalize(name + suffix)


# Đọc danh sách 35 đường dẫn
missing_paths = [
    line.strip()
    for line in MISSING_FILE.read_text(encoding="utf-8").splitlines()
    if line.strip()
]

# Lấy toàn bộ metadata Firestore
firestore_docs = []

for doc in collection.stream():
    data = doc.to_dict()

    firestore_docs.append({
        "id": doc.id,
        "word": data.get("word", ""),
        "fileName": data.get("fileName", ""),
        "storagePath": data.get("storagePath", "")
    })

# Tạo chỉ mục theo tên file
exact_lookup = defaultdict(list)
variant_lookup = defaultdict(list)

for doc in firestore_docs:
    filename = normalize(doc["fileName"])

    if filename:
        exact_lookup[filename].append(doc)

        variant_key = remove_variant_suffix(filename)
        variant_lookup[variant_key].append(doc)


print("\n===== KET QUA DOI CHIEU 35 FILE =====")
print("Tong file can kiem tra:", len(missing_paths))
print("Tong metadata Firestore:", len(firestore_docs))

for path in missing_paths:
    filename = Path(path).name
    exact_matches = exact_lookup.get(normalize(filename), [])

    print("\n----------------------------------------")
    print("LOCAL PATH:", path)

    if exact_matches:
        print("KET QUA: TIM THAY TEN FILE TRONG FIRESTORE")

        for doc in exact_matches:
            print("  Word:", doc["word"])
            print("  FileName:", doc["fileName"])
            print("  StoragePath:", doc["storagePath"])
            print("  Document ID:", doc["id"])

        continue

    variant_key = remove_variant_suffix(filename)
    variant_matches = variant_lookup.get(variant_key, [])

    if variant_matches:
        print("KET QUA: CO FILE BIEN THE, CAN KIEM TRA")

        for doc in variant_matches:
            print("  Word:", doc["word"])
            print("  FileName:", doc["fileName"])
            print("  StoragePath:", doc["storagePath"])
            print("  Document ID:", doc["id"])
    else:
        print("KET QUA: CHUA TIM THAY TEN FILE TUONG UNG")