<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Project;
use App\Entity\StudentGroup;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/student')]
class StudentController extends AbstractController
{
    #[Route('/{project}/new', name: 'app_student_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StudentRepository $studentRepository, Project $project): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $student->setProject($project);
            $studentRepository->save($student, true);
            return $this->redirectToRoute('app_project_status', ['id' => $project->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('student/new.html.twig', [
            'project' => $project,
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{project}/{id}', name: 'app_student_delete', methods: ['POST'])]
    public function delete(Request $request, Student $student, StudentRepository $studentRepository, Project $project): Response
    {
        if ($this->isCsrfTokenValid('delete'.$student->getId(), $request->request->get('_token'))) {
            $studentRepository->remove($student, true);
        }

        return $this->redirectToRoute('app_project_status', ['id' => $project->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/assign/{studentGroup}', name: 'app_student_assign', methods: ['POST'])]
    public function assignToProject(Request $request, Student $student, StudentGroup $studentGroup, StudentRepository $studentRepository): Response
    {
        $response = new JsonResponse();
        if($student->getProject()->getId() != $studentGroup->getProject()->getId())
        {
            $response->setData(['error' => 'Trying to assign student to a group of a project, to which student does not belong.']);
            $response->setStatusCode(JsonResponse::HTTP_NOT_ACCEPTABLE);
            return $response;
        }
        if($student->getStudentGroup() != null)
        {
            $response->setData(['error' => 'This student already has a group.']);
            $response->setStatusCode(JsonResponse::HTTP_NOT_ACCEPTABLE);
            return $response;
        }

        $student->setStudentGroup($studentGroup);
        $studentRepository->save($student, true);
        $response->setData([
            'success' => 'Success. Student was assigned to a group.'
        ]);
        return $response;
    }

}
