<?php

namespace App\Service;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
class UploaderHelper
{
    const PERSON_IMAGE = 'person_image';
    const USER_IMAGE = 'user_image';
    const EVENT_IMAGE = 'event_image';
    const LOCATION_IMAGE = 'location_image';

    /**
     * @var RequestStackContext
     */
    private $requestStackContext;
    /**
     * @var FilesystemInterface
     */
    private $filesystem;
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $publicAssetsBaseUrl;

    public function __construct(FilesystemInterface $uploadsFilesystem, RequestStackContext $requestStackContext, LoggerInterface $logger, string $uploadedAssetsBaseUrl)
    {
        $this->requestStackContext = $requestStackContext;
        $this->filesystem = $uploadsFilesystem;
        $this->logger = $logger;
        $this->publicAssetsBaseUrl = $uploadedAssetsBaseUrl;
    }

    public function uploadPersonImage(File $file, ?string $existingFilename): string
    {
        $newFilename = $this->uploadFile($file, self::PERSON_IMAGE, true);

        if($existingFilename) {
           try {
	        $result = $this->filesystem->delete(self::PERSON_IMAGE.'/'.$existingFilename);

	           if ($result === false) {
	                throw new \Exception(sprintf('Error deleting "%s"', $existingFilename));
	           }
            }
            catch (FileNotFoundException $e) {
                $this->logger->alert(sprintf('old file missing file "%s" trying to delete ', $existingFilename));
            }
        }
        return $newFilename;
    }

    public function uploadEventImage(File $file, ?string $existingFilename): string
    {
        $newFilename = $this->uploadFile($file, self::EVENT_IMAGE, true);

        if($existingFilename) {
            try {

                $result =  $this->filesystem->delete(self::EVENT_IMAGE.'/'. $existingFilename);
                if ($result === false) {
                    throw new \Exception(sprintf('Error deleting "%s"', $existingFilename));
                }
            }
            catch (FileNotFoundException $e) {
                $this->logger->alert(sprintf('old file missing file "%s" trying to delete ', $existingFilename));
            }
        }
        return $newFilename;
    }

    public function getPublicPath(string $path): string
    {
        return $this->requestStackContext->getBasePath() . $this->publicAssetsBaseUrl .'/'. $path;
    }

    private function uploadFile(File $file, string $directory, bool $isPublic) : string
    {
        if($file instanceof UploadedFile){
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }
        $newFilename = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME)).'-'.uniqid().'.'.$file->guessExtension();

        $stream = fopen($file->getPathname(), 'r');

        $result = $this->filesystem->writeStream(
            $directory.'/'.$newFilename,
            $stream
        );

        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }
        if(is_resource($stream)) {
            fclose($stream);
        }

        return $newFilename;
    }
}