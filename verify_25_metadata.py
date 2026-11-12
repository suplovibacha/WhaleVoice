import firebase_admin
from firebase_admin import credentials, firestore

KEY_PATH = r"D:\Projects\HCSDLDPT\firebase_key\serviceAccountKey.json"

if not firebase_admin._apps:
    cred = credentials.Certificate(KEY_PATH)
    firebase_admin.initialize_app(cred)

db = firestore.client()

doc_ids = [
    "8Qzf2qWdTCwxdiJZJklT",
    "word_00300", "word_00516", "word_00535",
    "word_00536", "word_00532", "word_00533",
    "word_00534", "word_00568", "word_00398",
    "word_00361", "word_02562", "word_02558",
    "word_02596", "word_02572", "word_02608",
    "word_02598", "word_02600", "word_02591",
    "word_02560", "word_03674", "word_03675",
    "word_03676", "word_03680", "word_03682"
]

print("===== KIEM TRA 25 METADATA =====")

success = 0

for doc_id in doc_ids:
    doc = db.collection("words").document(doc_id).get()

    if not doc.exists:
        print("THIEU:", doc_id)
        continue

    data = doc.to_dict()

    if all(data.get(k) for k in ["word", "letter", "fileName", "storagePath"]):
        success += 1
        print("OK:", data["word"], "->", data["storagePath"])
    else:
        print("THIEU TRUONG:", doc_id, data)

print("\n===== KET QUA =====")
print("Hop le:", success, "/ 25")
