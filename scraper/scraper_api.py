from flask import Flask, request, jsonify
import yfinance as yf
import logging

app = Flask(__name__)
logging.basicConfig(level=logging.INFO)


@app.route("/price", methods=["GET"])
def get_price():
    ticker = request.args.get("ticker")
    if not ticker:
        return jsonify({"error": "Missing ticker"}), 400

    try:
        price = yf.Ticker(ticker).fast_info["last_price"]
        if price is None or price != price:
            raise ValueError("no last_price")
        return jsonify({"ticker": ticker, "price": float(price)})
    except Exception as e:
        app.logger.warning("price fetch failed for %s: %s", ticker, e)
        return jsonify({"error": "Price not found", "ticker": ticker}), 404


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001)
