from fastapi import FastAPI, Form, HTTPException
from database import reviews_collection
from fastapi.middleware.cors import CORSMiddleware

app = FastAPI(title="Review Service")

# =========================
# CORS
# =========================
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:9197", "http://127.0.0.1:9197", "http://192.168.1.39:9197"],  # Izinkan origin frontend
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# =========================
# POST: Create Review
# =========================
@app.post("/review")
def create_review(
    product_id: int = Form(...),
    review: str = Form(...),
    rating: int = Form(...)
):
    if rating < 1 or rating > 5:
        raise HTTPException(
            status_code=400,
            detail="Rating must be between 1 and 5"
        )

    data = {
        "product_id": product_id,
        "review": review,
        "rating": rating
    }

    result = reviews_collection.insert_one(data)

    return {
        "success": True,
        "message": "Review created successfully",
        "data": {
            "product_id": product_id,
            "review": review,
            "rating": rating
        },
        "id": str(result.inserted_id)
    }

# =========================
# GET: All Reviews
# =========================
@app.get("/reviews")
def get_reviews():
    reviews = list(
        reviews_collection.find({}, {"_id": 0})
    )
    return {
        "success": True,
        "data": reviews
    }

# =========================
# GET: Reviews by Product
# =========================
@app.get("/reviews/{product_id}")
def get_reviews_by_product(product_id: int):
    reviews = list(
        reviews_collection.find(
            {"product_id": product_id},
            {"_id": 0}
        )
    )
    return {
        "success": True,
        "data": reviews
    }
