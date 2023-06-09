<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ReservationRoom;
use App\Models\Room;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class RoomController extends Controller
{
    public function index()
    {
        $list_rooms = DB::table('rooms')->get();

        return response()->json([
            'message' => 'Query successfully!',
            'status' => 200,
            'list_rooms' => $list_rooms,
        ], 200);
    }

    public function show($id)
    {
        $room = DB::table('rooms')->where('id', '=', $id)->first();

        if ($room) {
            return response()->json([
                'message' => 'Query successfully!',
                'status' => 200,
                'room' => $room,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Data not found!',
                'status' => 404,
                'room' => $room,
            ], 404);
        }
    }

    public function getRoomsByRoomTypeId($id)
    {
        $list_rooms = DB::table('rooms')->where('room_type_id', '=', $id)->get();
        $data = [];
        foreach ($list_rooms as $item) {
            $area = DB::table('areas')->where('id', '=', $item->area_id)->first();
            $floor = DB::table('floors')->where('id', '=', $item->area_id)->first();

            $data[] = [
                'id_room' => $item->id,
                'room_name' => $item->room_name,
                'area_name' => $area->area_name,
                'floor_name' => $floor->floor_name,
            ];
        }
        return response()->json([
            'status' => 200,
            'list_rooms' => $data,
        ]);
    }
    public function updateRoom(Request $request, $id)
    {
        $room = Room::find($id);
        if ($room) {

            if ($request->room_name) {
                $room->room_name = $request->room_name;
            }
            if ($request->room_type_id) {
                $room->room_type_id = $request->room_type_id;
            }
            if ($request->area_id) {
                $room->area_id = $request->area_id;
            }
            if ($request->floor_id) {
                $room->floor_id = $request->floor_id;
            }

            $room->update();
            return response()->json([
                'message' => 'Update successfully!',
                'status' => 200,
                'room_type' => $room,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Data not found!',
                'status' => 404,
            ], 404);
        }
    }
    public function storeRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_name',
            'room_type_id',
            'area_id',
            'floor_id',

        ]);

        if ($validator->fails()) {
            $response = [
                'status_code' => 400,
                'message' => $validator->errors(),
            ];
            return response()->json($response, 400);
        }
        // $employee = Employee::create([
        $room = Room::create([
            'room_name' => $request->room_name,
            'room_type_id' => $request->room_type_id,
            'area_id' => $request->area_id,
            'floor_id' => $request->floor_id,
            'status' =>  '0',

        ]);
        // ]);
        // $position = Position::find($data['position_id']);
        if (!$room) {
            return response()->json([
                'message' => 'Data not found!',
                'status' => 400,
            ], 400);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Room created Successfully',
                'employee' => $room,
            ]);
        }
    }
    public function getReservedRooms(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'time_start',
            'time_end',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'validate' => true,
                'message' => 'you need to enter enough',
            ]);
        }
        $timeStart = $request->time_start;
        $timeEnd = $request->time_end;
        $reservedRooms1 = ReservationRoom::whereDate('time_start', '>=',  $timeStart)
            ->whereDate('time_end', '<=', $timeEnd)
            ->get();
        $reservedRooms2 = ReservationRoom::whereDate('time_start', '<',  $timeStart)
            ->whereDate('time_end', '>', $timeStart)
            ->get();
        $reservedRooms3 = ReservationRoom::whereDate('time_start', '<',  $timeEnd)
            ->whereDate('time_end', '>', $timeEnd)
            ->get();
        $reservedRooms4 = ReservationRoom::whereDate('time_start', '<',  $timeStart)
            ->whereDate('time_end', '>', $timeEnd)
            ->get();
        $reservedRooms = Collection::empty();
        $reservedRooms = $reservedRooms->concat($reservedRooms1);
        $reservedRooms = $reservedRooms->concat($reservedRooms2);
        $reservedRooms = $reservedRooms->concat($reservedRooms3);
        $reservedRooms = $reservedRooms->concat($reservedRooms4);
        $data = [];
        foreach ($reservedRooms as $item) {
            $number = DB::table('rooms')->where('id', '=', $item->room_id)
                ->where('room_type_id', '=', $id)->first();
            if ($number !== null) {
                $data[] = $number;
            }
        }

        $data = collect($data)
            ->reject(function ($item) {
                return empty($item);
            })
            ->unique()
            ->values()
            ->all();

        $data3 = [];
        $areas = DB::table('areas')->get();
        foreach ($areas as $area) {
            $floors = DB::table('floors')->get();
            $data2 = [];
            foreach ($floors as $floor) {
                $data1 = [];
                foreach ($data as $item1) {
                    $room = DB::table('rooms')
                        ->where('id', '=', $item1->id)
                        ->where('area_id', '=', $area->id)
                        ->where('floor_id', '=', $floor->id)
                        ->get();
                    if (count($room) != 0) {
                        $data1 = $room;
                    }
                }
                $data2[] = [
                    "floor_name" => $floor->floor_name,
                    "list_rooms" => $data1,
                ];
            }
            $data3[] = [
                "area_name" => $area->area_name,
                "list_floors" => $data2
            ];
        }
        return response()->json([
            'data' => $data3
        ]);
    }
}
