import firebase_admin
from firebase_admin import credentials, firestore
from google.cloud.firestore_v1.base_query import FieldFilter

# Cau hinh Firebase
KEY_PATH = r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"

if not firebase_admin._apps:
    cred = credentials.Certificate(KEY_PATH)
    firebase_admin.initialize_app(cred)

db = firestore.client()

# 10 tu can kiem tra
words = [
    "đĩa", "địa", "đưa", "hát", "hàu",
    "hẳn", "phen", "quết", "quật", "sòn"
]

paths = [
    "D_STROKE/d-ia4.wav",
    "D_STROKE/d-ia5.wav",
    "D_STROKE/d-uua0.wav",
    "H/hat1.wav",
    "H/hau2.wav",
    "H/haan3.wav",
    "P/phen0.wav",
    "Q/que+t1.wav",
    "Q/qua+t5.wav",
    "S/son2.wav"
]

results = {}

# Tim theo word
word_docs = (
    db.collection("words")
    .where(filter=FieldFilter("word", "in", words))
    .stream()
)

for doc in word_docs:
    results[doc.id] = doc.to_dict()

# Tim theo storagePath
path_docs = (
    db.collection("words")
    .where(filter=FieldFilter("storagePath", "in", paths))
    .stream()
)

for doc in path_docs:
    results[doc.id] = doc.to_dict()
# Tim them theo fileName
file_names = [
    "hat1.wav",
    "hau2.wav",
    "haan3.wav",
    "phen0.wav",
    "son2.wav"
]

file_docs = (
    db.collection("words")
    .where(filter=FieldFilter("fileName", "in", file_names))
    .stream()
)

for doc in file_docs:
    results[doc.id] = doc.to_dict()

print("\n========== KET QUA FIRESTORE ==========")

if not results:
    print("Khong tim thay document nao.")
else:
    for doc_id, data in results.items():
        print("\nDocument ID:", doc_id)
        print("Word:", data.get("word"))
        print("Letter:", data.get("letter"))
        print("FileName:", data.get("fileName"))
        print("StoragePath:", data.get("storagePath"))

print("\n========== TONG KET ==========")
print("Tong document tim thay:", len(results))
print("Day la thao tac chi doc, khong cap nhat Firestore.")