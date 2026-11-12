
import json
from pathlib import Path

AUDIT = Path(
    r"D:\Projects\HCSDLDPT\WhaleVoice\storage\app\firestore_words_audit.json"
)

with open(AUDIT, "r", encoding="utf-8-sig") as f:
    data = json.load(f)

def search(obj):
    if isinstance(obj, dict):
        word = str(obj.get("word", "")).strip().lower()
        filename = str(obj.get("fileName", "")).lower()
        path = str(obj.get("storagePath", "")).lower()

        if (
            "quật" in word
            or "quết" in word
            or "qua+t5" in filename
            or "que+t1" in filename
            or "qua+t5" in path
            or "que+t1" in path
        ):
            print("--------------------------------")
            print("ID:", obj.get("id"))
            print("Word:", obj.get("word"))
            print("FileName:", obj.get("fileName"))
            print("StoragePath:", obj.get("storagePath"))

        for value in obj.values():
            search(value)

    elif isinstance(obj, list):
        for item in obj:
            search(item)

print("===== TIM QUAT VA QUET TRONG AUDIT CU =====")
search(data)
print("===== HOAN TAT =====")
