from fastapi import FastAPI, Form, HTTPException
from database import reviews_collection
from fastapi.middleware.cors import CORSMiddleware

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],   # sementara
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app = FastAPI(title="Review Service")


# =========================
# POST: Create Review
# =========================
@app.post("/review")
def create_review(
    product_id: int = Form(...),
    review: str = Form(...),
    rating: int = Form(...)
):
    try:
        # validasi sederhana
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

        # insert ke Mongo
        result = reviews_collection.insert_one(data)

        # RESPONSE AMAN (TIDAK return dict yg sudah kena ObjectId)
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

    except HTTPException as e:
        raise e

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=str(e)
        )


# =========================
# GET: All Reviews
# =========================
@app.get("/reviews")
def get_reviews():
    try:
        reviews = list(
            reviews_collection.find({}, {"_id": 0})
        )

        return {
            "success": True,
            "data": reviews
        }

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=str(e)
        )


# =========================
# GET: Reviews by Product
# =========================
@app.get("/reviews/{product_id}")
def get_reviews_by_product(product_id: int):
    try:
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

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=str(e)
        )
