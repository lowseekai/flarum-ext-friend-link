<?php

namespace Nodeloc\FriendLink\Logic;

use Flarum\Foundation\ValidationException;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Nodeloc\FriendLink\Model\FriendLink;
use Psr\Http\Message\UploadedFileInterface;

class AddLogic
{
    protected Filesystem $uploadDir;

    public function __construct(
        Factory $filesystemFactory,
        protected ImageManager $imageManager
    ) {
        $this->uploadDir = $filesystemFactory->disk('flarum-avatars');
    }

    public function save($actor, array $data, ?UploadedFileInterface $file): array
    {
        $siteName = trim((string) ($data['sitename'] ?? ''));
        $siteUrl = trim((string) ($data['siteurl'] ?? ''));

        if ($siteName === '' || mb_strlen($siteName) > 100) {
            throw new ValidationException([
                'sitename' => '网站名称不能为空，且不能超过 100 个字符。',
            ]);
        }

        if (! $this->isValidUrl($siteUrl)) {
            throw new ValidationException([
                'siteurl' => '请输入有效的网站地址。',
            ]);
        }

        if (! $file) {
            throw new ValidationException([
                'sitelogo' => '请选择网站 Logo。',
            ]);
        }

        $this->assertFileRequired($file);
        $this->assertFileMimes($file);
        $this->assertFileSize($file);

        $exists = FriendLink::where([
            'user_id' => $actor->id,
            'siteurl' => $siteUrl,
            'status' => 1,
        ])->exists();

        if ($exists) {
            throw new ValidationException([
                'siteurl' => '您已经分享过这个网站。',
            ]);
        }

        $uploadedSitelogo = $this->upload($file);

        FriendLink::create([
            'user_id' => $actor->id,
            'status' => 2,
            'created_time' => time(),
            'update_time' => time(),
            'sitename' => $siteName,
            'siteurl' => $siteUrl,
            'sitelogourl' => $uploadedSitelogo,
            'img_list' => '',
            'cover_width' => 100,
            'cover_height' => 100,
            'like_count' => 0,
            'view_count' => 0,
            'exchange_count' => 0,
        ]);

        return [
            'status' => true,
            'msg' => '提交成功，请等待管理员审核。',
        ];
    }

    public function upload(UploadedFileInterface $file): string
    {
        $stream = $file->getStream();
        $stream->rewind();
        $contents = $stream->getContents();

        if ($contents === '') {
            throw new ValidationException([
                'sitelogo' => '读取图片失败。',
            ]);
        }

        try {
            $image = $this->imageManager
                ->read($contents)
                ->cover(100, 100)
                ->toPng()
                ->toString();
        } catch (\Throwable) {
            throw new ValidationException([
                'sitelogo' => '图片处理失败，请更换图片后重试。',
            ]);
        }

        $filename = Str::random(40).'.png';
        $this->uploadDir->put($filename, $image);

        return $this->uploadDir->url($filename);
    }

    protected function isValidUrl(string $url): bool
    {
        return (bool) filter_var($url, FILTER_VALIDATE_URL)
            && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
    }

    protected function assertFileRequired(UploadedFileInterface $file): void
    {
        $error = $file->getError();

        if ($error === UPLOAD_ERR_OK) {
            return;
        }

        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new ValidationException([
                'sitelogo' => '图片文件过大。',
            ]);
        }

        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new ValidationException([
                'sitelogo' => '图片不能为空。',
            ]);
        }

        throw new ValidationException([
            'sitelogo' => '图片上传失败。',
        ]);
    }

    protected function assertFileMimes(UploadedFileInterface $file): void
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/bmp', 'image/gif'];
        $mime = strtolower((string) $file->getClientMediaType());
        $extension = strtolower((string) pathinfo((string) $file->getClientFilename(), PATHINFO_EXTENSION));

        if (! in_array($mime, $allowedTypes, true) || in_array($extension, ['php', 'php3', 'php4', 'php5', 'phtml'], true)) {
            throw new ValidationException([
                'sitelogo' => '只允许上传 JPG、PNG、BMP 或 GIF 图片。',
            ]);
        }

        $stream = $file->getStream();
        $stream->rewind();
        $contents = $stream->getContents();

        try {
            $this->imageManager->read($contents);
        } catch (\Throwable) {
            throw new ValidationException([
                'sitelogo' => '上传的文件不是有效图片。',
            ]);
        }
    }

    protected function assertFileSize(UploadedFileInterface $file): void
    {
        if (($file->getSize() ?? 0) > 2 * 1024 * 1024) {
            throw new ValidationException([
                'sitelogo' => '图片大小不能超过 2 MB。',
            ]);
        }
    }
}
