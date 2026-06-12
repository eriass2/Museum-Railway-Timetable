"""Repository paths for Lennakatten fixture scripts."""

from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[3]
FIXTURE_DIR = REPO_ROOT / "testdata" / "fixtures" / "lennakatten"
REFERENCE_PDF_DIR = REPO_ROOT / "testdata" / "reference-pdfs"
