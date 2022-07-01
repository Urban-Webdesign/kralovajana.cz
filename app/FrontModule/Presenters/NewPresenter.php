<?php declare(strict_types = 1);

namespace App\FrontModule\Presenters;

use K2D\Gallery\Models\GalleryModel;
use K2D\Gallery\Models\ImageModel;
use K2D\News\Models\NewModel;
use Nette\Utils\Paginator;

class NewPresenter extends BasePresenter
{

    /** @inject */
    public GalleryModel $galleryModel;

    /** @inject */
    public ImageModel $imageModel;

    /** @inject */
    public NewModel $newModel;

    public function renderDefault(int $page = 1): void
    {
        $publicNewsCount = $this->repository->getPublicNewsCount('cs');
        $this->template->newsCount = $publicNewsCount;
        $itemsPerPage = 6;

        $paginator = new Paginator;
        $paginator->setPage($page); // číslo aktuální stránky
        $paginator->setItemsPerPage($itemsPerPage); // počet položek na stránce
        $paginator->setItemCount($publicNewsCount); // celkový počet položek, je-li znám

        $this->template->news = $this->newModel->getPublicNews('cs')->limit($paginator->getLength(), $paginator->getOffset());
        $this->template->paginator = $paginator;
    }

    public function renderShow(string $slug): void
    {
        $new = $this->newModel->getNew($slug, 'cs');
        $this->template->new = $new;

        if (isset($new->gallery_id))
            $this->template->images = $this->imageModel->getImagesByGallery($new->gallery_id);
    }

}