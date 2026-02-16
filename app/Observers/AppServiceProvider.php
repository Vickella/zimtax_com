use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Observers\SalesInvoiceObserver;
use App\Observers\PurchaseInvoiceObserver;

public function boot(): void
{
    SalesInvoice::observe(SalesInvoiceObserver::class);
    PurchaseInvoice::observe(PurchaseInvoiceObserver::class);
}
