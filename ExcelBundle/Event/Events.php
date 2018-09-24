<?php

namespace Nik\ExcelBundle\Event;

/**
 * Contains all the events triggered by the bundle.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
final class Events
{
    /**
     * Triggered before a file upload is handled.
     *
     * @note This event is the same for new and old entities.
     *
     * @Event("Nik\SystemBundle\Vendor\ExcelBundle\Event\Event")
     */
    const PRE_UPLOAD    = 'nik_excel.pre_upload';

    /**
     * Triggered right after a file upload is handled.
     *
     * @note This event is the same for new and old entities.
     *
     * @Event("Nik\SystemBundle\Vendor\ExcelBundle\Event\Event")
     */
    const POST_UPLOAD   = 'nik_excel.post_upload';

    /**
     * Triggered before a file is injected into an entity.
     *
     * @Event("Nik\SystemBundle\Vendor\ExcelBundle\Event\Event")
     */
    const PRE_INJECT    = 'nik_excel.pre_inject';

    /**
     * Triggered after a file is injected into an entity.
     *
     * @Event("Nik\SystemBundle\Vendor\ExcelBundle\Event\Event")
     */
    const POST_INJECT   = 'nik_excel.post_inject';
}
