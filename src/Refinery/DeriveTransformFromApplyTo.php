<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Refinery;

use ILIAS\Data\Result;
use Exception;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
trait DeriveTransformFromApplyTo
{
    abstract public function applyTo(Result $result) : Result;

    /**
     * @param mixed $from
     * @return mixed
     * @throws Exception
     */
    public function transform($from)
    {
        /** @var Result $result */
        $result = $this->applyTo(new Result\Ok($from));
        if (true === $result->isError()) {
            $error = $result->error();

            if ($error instanceof Exception) {
                throw $error;
            }

            throw new Exception($error);
        }
        return $result->value();
    }
}
