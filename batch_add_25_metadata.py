import firebase_admin
from firebase_admin import credentials, firestore
from google.cloud.firestore_v1.base_query import FieldFilter

KEY_PATH = r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"

DATA = [
    ("bầu",   "B", "ba+u2.wav"),
    ("cạch",  "C", "cach5.wav"),
    ("chạy",  "C", "chay5.wav"),
    ("chễ",   "C", "che+4.wav"),
    ("chệ",   "C", "che+5.wav"),
    ("chênh", "C", "che+nh0.wav"),
    ("chềnh", "C", "che+nh2.wav"),
    ("chểnh", "C", "che+nh3.wav"),
    ("chính", "C", "chinh1.wav"),
    ("cọng",  "C", "cong5.wav"),
    ("cơn",   "C", "coon0.wav"),
    ("quận",  "Q", "qua+n5.wav"),
    ("quằn",  "Q", "quaan2.wav"),
    ("quách", "Q", "quach1.wav"),
    ("quái",  "Q", "quai1.wav"),
    ("quan",  "Q", "quan0.wav"),
    ("quán",  "Q", "quan1.wav"),
    ("quyết", "Q", "quye+t1.wav"),
    ("quýnh", "Q", "quynh1.wav"),
    ("quýt",  "Q", "quyt1.wav"),
    ("un",    "U", "un0.wav"),
    ("ùn",    "U", "un2.wav"),
    ("ủn",    "U", "un3.wav"),
    ("uốn",   "U", "uo+n1.wav"),
    ("uống",  "U", "uo+ng1.wav"),
]

if not firebase_admin._apps:
    cred = credentials.Certificate(KEY_PATH)
    firebase_admin.initialize_app(cred)

db = firestore.client()
collection = db.collection("words")

words = [x[0] for x in DATA]
filenames = [x[2] for x in DATA]

print("===== KIEM TRA FIRESTORE =====")

word_docs = list(
    collection.where(filter=FieldFilter("word", "in", words)).stream()
)

file_docs = list(
    collection.where(filter=FieldFilter("fileName", "in", filenames)).stream()
)

by_word = {}
by_filename = {}

for doc in word_docs:
    by_word.setdefault(doc.to_dict().get("word"), []).append(doc)

for doc in file_docs:
    by_filename.setdefault(doc.to_dict().get("fileName"), []).append(doc)

batch = db.batch()
updates = 0
creates = 0

for word, letter, filename in DATA:
    storage_path = f"{letter}/{filename}"

    matches = {}
    for doc in by_word.get(word, []):
        matches[doc.id] = doc
    for doc in by_filename.get(filename, []):
        matches[doc.id] = doc

    if len(matches) > 1:
        raise RuntimeError(
            f"DUNG LAI: Co nhieu document lien quan den {word}: "
            f"{list(matches.keys())}"
        )

    if matches:
        doc = next(iter(matches.values()))
        old = doc.to_dict()

        if old.get("word") not in (None, word):
            raise RuntimeError(
                f"DUNG LAI: Filename {filename} dang thuoc tu "
                f"{old.get('word')}"
            )

        batch.update(doc.reference, {
            "word": word,
            "letter": letter,
            "fileName": filename,
            "storagePath": storage_path
        })

        updates += 1
        print(f"CAP NHAT: {word} -> {storage_path} [{doc.id}]")

    else:
        ref = collection.document()
        batch.set(ref, {
            "word": word,
            "letter": letter,
            "fileName": filename,
            "storagePath": storage_path
        })

        creates += 1
        print(f"TAO MOI: {word} -> {storage_path} [{ref.id}]")

print("\n===== TONG KET =====")
print("Tong so:", len(DATA))
print("Can cap nhat:", updates)
print("Can tao moi:", creates)

if updates + creates != 25:
    raise RuntimeError("So luong thay doi khong hop le. Khong ghi Firestore.")

print("\nDang ghi batch...")
batch.commit()

print("===== HOAN TAT =====")
print("Da ghi thanh cong", updates + creates, "document.")
