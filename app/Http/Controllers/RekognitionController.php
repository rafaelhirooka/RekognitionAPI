<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Aws\Rekognition\Exception\RekognitionException;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class RekognitionController extends Controller
{
    use ApiResponser;

    private $client;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new RekognitionClient([
            'profile' => 'rekognition',
            'version' => '2016-06-27',
            'region' => 'us-west-2'
        ]);
    }

    public function detectLabels(Request $request) {
        try {
            $data = $request->all();

            $v = Validator::make($data, [
                'image' => 'required'
            ]);

            if ($v->fails())
                throw new \RuntimeException('Verifique se enviou todos os campos', Response::HTTP_UNPROCESSABLE_ENTITY);

            if (strpos($data['image'], ',') !== false)
                $data['image'] = explode(',', $data['image'])[1];


            $tmpfname = tempnam(sys_get_temp_dir(), mt_rand(10,99999));

            $handle = fopen($tmpfname, "w");
            fwrite($handle, base64_decode($data['image']));
            fclose($handle);

            if (file_exists($tmpfname)) {
                $image = file_get_contents($tmpfname);

                unlink($tmpfname);

                $result = $this->client->detectLabels([
                    'Image' => [
                        'Bytes' => $image
                    ],
                    'Attributes' => array('ALL')
                ]);

            } else {
                throw new \RuntimeException('Problema no arquivo temporário.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return $this->successResponse($result->toArray());

        } catch (RekognitionException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
