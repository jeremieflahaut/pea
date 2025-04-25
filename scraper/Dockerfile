FROM python:slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY scraper_api.py .

EXPOSE 5001

CMD ["python", "scraper_api.py"]
