<?php
/**
 * File containing the FeedbackController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace EzSystems\DemoBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use EzSystems\DemoBundle\Entity\Feedback;
use Symfony\Component\HttpFoundation\Response;

class FeedbackFormController extends Controller
{
    /**
     * Displays and manages the feedback form.
     *
     * The signature of this method follows the one from the default view controller.
     * @param $locationId
     * @param $viewType
     * @param bool $layout
     * @param array $params
     *
     * @return mixed
     */
    public function showFeedbackFormAction( $locationId, $viewType, $layout = false, array $params = array() )
    {
        // Creating a form using Symfony's form component
        $feedback = new Feedback();
        $form = $this->createFormBuilder( $feedback )
            ->add( 'firstName', 'text' )
            ->add( 'lastName', 'text' )
            ->add( 'email', 'email' )
            ->add( 'subject', 'text' )
            ->add( 'country', 'country' )
            ->add( 'message', 'textarea' )
            ->add( 'save', 'submit' )
            ->getForm();

        $form->handleRequest( $this->getRequest() );
        $displaySentMsg = false;

        if ( $form->isValid() )
        {
            // Sending an email using Swift Mailer: if the form is meant to be used at several places
            // in your application, upgrade it with a form type as explained in symfony's form component documentation
            $message = \Swift_Message::newInstance()
                 ->setSubject( $this->container->getParameter( 'ezdemo.feedback_form.subject' ) )
                 ->setFrom( $this->container->getParameter( 'ezdemo.feedback_form.mail_from' ) )
                 ->setTo( $this->container->getParameter( 'ezdemo.feedback_form.mail_to' ) )
                 ->setBody(
                     $this->renderView(
                         'eZDemoBundle:email:feedback_form.txt.twig',
                         array(
                             'firstname' => $feedback->firstName,
                             'lastname' => $feedback->lastName,
                             'email' => $feedback->email,
                             'subject' => $feedback->subject,
                             'country' => $feedback->country,
                             'message' => $feedback->message
                         )
                     )
                 );

            $this->get( 'mailer' )->send( $message );
            $displaySentMsg = true;
        }

        return $this->get( 'ez_content' )->viewLocation(
            $locationId,
            $viewType,
            $layout,
            array(
                'form' => $form->createView(),
                'display_sent_msg' => $displaySentMsg
            )
        );
    }
}
