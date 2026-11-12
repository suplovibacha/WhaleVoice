
from pathlib import Path
import wave

SOURCE = Path(r"D:\Projects\HCSDLDPT\source")

files = [
    ("C/che+5.wav", "C/che+5-2.wav"),
    ("C/cach5.wav", "C/cach5-2.wav"),
    ("C/chay5.wav", "C/chay5-2.wav"),
]

def get_info(relative_path):
    path = SOURCE / relative_path

    with wave.open(str(path), "rb") as wav:
        frames = wav.getnframes()
        rate = wav.getframerate()

        return {
            "file": path.name,
            "size": path.stat().st_size,
            "duration": frames / rate,
            "sample_rate": rate,
            "channels": wav.getnchannels(),
            "sample_width": wav.getsampwidth(),
        }

for original, duplicate in files:
    print("\n" + "=" * 60)

    for relative in (original, duplicate):
        info = get_info(relative)

        print("\nFile:", info["file"])
        print("Dung luong:", info["size"], "bytes")
        print("Thoi luong:", round(info["duration"], 3), "giay")
        print("Sample rate:", info["sample_rate"], "Hz")
        print("Channels:", info["channels"])
        print("Sample width:", info["sample_width"], "bytes")
