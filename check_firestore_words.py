import firebase_admin
from firebase_admin import credentials, firestore
import sys

sys.stdout.reconfigure(encoding="utf-8")

cred = credentials.Certificate(
    r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"
)

if not firebase_admin._apps:
    firebase_admin.initialize_app(cred)

db = firestore.client()

words = ["mỹ", "vía", "vô", "nhật", "giai"]

filenames = [
    "my4.wav",
    "via1.wav",
    "vo+0.wav",
    "nha+t5.wav",
    "giai0.wav"
]

print("\n===== KIEM TRA THEO TEN TU =====")

word_query = (
    db.collection("words")
    .where("word", "in", words)
)

for doc in word_query.stream():
    print(doc.id, doc.to_dict())

print("\n===== KIEM TRA THEO TEN FILE =====")

file_query = (
    db.collection("words")
    .where("fileName", "in", filenames)
)

for doc in file_query.stream():
    print(doc.id, doc.to_dict())

print("\n===== HOAN TAT KIEM TRA =====")