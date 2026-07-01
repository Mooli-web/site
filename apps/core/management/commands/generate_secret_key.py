"""تولید یک SECRET_KEY امن و تصادفی برای محیط production."""
from django.core.management.base import BaseCommand
from django.core.management.utils import get_random_secret_key


class Command(BaseCommand):
    help = "تولید یک SECRET_KEY امن برای قرار دادن در فایل .env محیط production."

    def handle(self, *args, **options):
        key = get_random_secret_key()
        self.stdout.write(self.style.SUCCESS("SECRET_KEY تولیدشده (در .env قرار دهید):\n"))
        self.stdout.write(f"SECRET_KEY={key}")
