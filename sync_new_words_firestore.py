
import firebase_admin
from firebase_admin import credentials, firestore
from pathlib import Path
import sys

sys.stdout.reconfigure(encoding="utf-8")

# ==============================
# 1. CẤU HÌNH
# ==============================

CREDENTIAL_PATH = (
    r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"
)

LOCAL_ROOT = Path(r"D:\Projects\HCSDLDPT\source")

# False: chỉ kiểm tra, chưa ghi Firestore
# True: thực sự thêm dữ liệu
APPLY_CHANGES = True

NEW_WORDS = [
    {
        "word": "hát",
        "letter": "H",
        "fileName": "hat1.wav",
        "storagePath": "H/hat1.wav"
    },
    {
        "word": "hàu",
        "letter": "H",
        "fileName": "hau2.wav",
        "storagePath": "H/hau2.wav"
    },
    {
        "word": "hẳn",
        "letter": "H",
        "fileName": "haan3.wav",
        "storagePath": "H/haan3.wav"
    },
    {
        "word": "phen",
        "letter": "P",
        "fileName": "phen0.wav",
        "storagePath": "P/phen0.wav"
    },
    {
        "word": "sòn",
        "letter": "S",
        "fileName": "son2.wav",
        "storagePath": "S/son2.wav"
    }
]# ==============================
# 2. KẾT NỐI FIRESTORE
# ==============================

cred = credentials.Certificate(CREDENTIAL_PATH)

if not firebase_admin._apps:
    firebase_admin.initialize_app(cred)

db = firestore.client()
collection = db.collection("words")

# ==============================
# 3. KIỂM TRA VÀ ĐỒNG BỘ
# ==============================

print("\n===== BAT DAU KIEM TRA =====")

for item in NEW_WORDS:

    word = item["word"]
    filename = item["fileName"]
    local_path = LOCAL_ROOT / item["storagePath"]

    print(f"\n--- {word} ---")

    # Kiểm tra file WAV
    if not local_path.is_file():
        print(f"THIEU FILE WAV: {local_path}")
        continue

    if local_path.stat().st_size == 0:
        print(f"FILE WAV RONG: {local_path}")
        continue

    # Kiểm tra trùng từ
    word_docs = list(
        collection.where("word", "==", word).stream()
    )

    # Kiểm tra trùng tên file
    file_docs = list(
        collection.where("fileName", "==", filename).stream()
    )

    if word_docs or file_docs:
        print("BO QUA: Phat hien du lieu trung.")

        for doc in word_docs:
            print("Trung word:", doc.id, doc.to_dict())

        for doc in file_docs:
            print("Trung fileName:", doc.id, doc.to_dict())

        continue

    # Hiển thị dữ liệu dự kiến
    print("FILE WAV: OK")
    print("DU LIEU DU KIEN:", item)

    if APPLY_CHANGES:
        collection.add(item)
        print("DA THEM VAO FIRESTORE")
    else:
        print("CHE DO KIEM TRA - CHUA GHI FIRESTORE")

print("\n===== HOAN TAT =====")
