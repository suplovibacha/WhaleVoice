import firebase_admin
from firebase_admin import credentials, firestore
from google.cloud.firestore_v1.base_query import FieldFilter

KEY_PATH = r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"

if not firebase_admin._apps:
    firebase_admin.initialize_app(
        credentials.Certificate(KEY_PATH)
    )

db = firestore.client()

filenames = ["bau+u2.wav", "ne+m5.wav", "tro1.wav"]

print("===== KIEM TRA 3 FILENAME =====")

query = (
    db.collection("words")
    .where(filter=FieldFilter("fileName", "in", filenames))
)

for doc in query.stream():
    data = doc.to_dict()
    print("--------------------------------")
    print("ID:", doc.id)
    print("Word:", data.get("word"))
    print("FileName:", data.get("fileName"))
    print("StoragePath:", data.get("storagePath"))
    print("Letter:", data.get("letter"))

print("===== HOAN TAT =====")
