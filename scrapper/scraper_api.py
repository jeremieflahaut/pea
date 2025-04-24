from flask import Flask, request, jsonify
from bs4 import BeautifulSoup
import requests

app = Flask(__name__)

@app.route("/price", methods=["GET"])
def get_price():
    ticker = request.args.get("ticker")
    if not ticker:
        return jsonify({"error": "Missing ticker"}), 400

    url = f"https://finance.yahoo.com/quote/{ticker}"
    headers = {"User-Agent": "Mozilla/5.0"}
    response = requests.get(url, headers=headers)
    soup = BeautifulSoup(response.text, "html.parser")

    price_span = soup.find("span", {"data-testid": "qsp-price"})
    if price_span:
        return jsonify({"ticker": ticker, "price": float(price_span.text.replace(',', ''))})

    return jsonify({"error": "Price not found"}), 404

if __name__ == "__main__":
    print("✅ Scraper Yahoo API running on http://localhost:5001")
    app.run(host="0.0.0.0", port=5001)
