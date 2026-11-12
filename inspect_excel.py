import pandas as pd
from pathlib import Path

EXCEL_PATH = Path(
    r"D:\Projects\HCSDLDPT\CƠ_SỞ_DỮ_LIỆU_TIẾNG_VIỆT_đã_mã_hóa.xlsx"
)

TARGETS = [
    "d-ia4", "d-ia5", "d-uua0",
    "hat1", "hau2", "haan3", "phen0",
    "que+t1", "qua+t5", "son2",
    "bau+u2", "d-it5x", "naac6",
    "ne+m5", "tro1"
]

output = []

xls = pd.ExcelFile(EXCEL_PATH)

for sheet in xls.sheet_names:
    df = pd.read_excel(
        EXCEL_PATH,
        sheet_name=sheet,
        header=None,
        dtype=str
    )

    for i, row in df.iterrows():
        values = [
            str(v).strip()
            for v in row
            if pd.notna(v) and str(v).strip()
        ]

        line = " | ".join(values)

        matched = [
            name for name in TARGETS
            if name.lower() in line.lower()
        ]

        if matched:
            output.append(
                f"Sheet: {sheet} | Dong: {i + 1}\n"
                f"Noi dung: {line}\n"
                f"File: {', '.join(matched)}\n"
                + "-" * 70
            )

Path("excel_target_rows.txt").write_text(
    "\n".join(output),
    encoding="utf-8"
)

print("Da xuat:", len(output), "dong")
print("File: excel_target_rows.txt")