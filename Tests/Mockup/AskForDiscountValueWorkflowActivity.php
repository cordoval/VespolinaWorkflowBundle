<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * (c) Daniel Kucharski <daniel@xerias.be>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\WorkflowBundle\Tests\Mockup;

use Vespolina\WorkflowBundle\Model\WorkflowActivity;

class AskForDiscountValueWorkflowActivity extends WorkflowActivity {

    public function execute()
    {

        if( !$discount = $this->workflowContainer->get('discount') )
        {
            $this->log('discount value is still empty, suspending activity');
            $this->suspend();

        }



    }
}
