<?php

namespace App\Controllers;
use App\Models\PlaylistModel;
use App\Models\PlayListMusicModel;
use App\Models\MusicModel;

class MusicController extends BaseController
{
    public function index()
{
    $musicModel = new MusicModel();
    $musiclist = $musicModel->findAll();
    $playlistModel = new PlaylistModel();
    $playlists = $playlistModel->findAll();

    return view('music_player', ['musiclist' => $musiclist, 'playlists' => $playlists]);
}


    public function createPlaylist()
    {
        $playlistName = $this->request->getPost('playlist_name');

        $playlistModel = new PlaylistModel();
        $playlistModel->insert(['song_name' => $playlistName]);

        return redirect()->to('/');
    }
    public function getPlaylist($playlistid)
    {
        $playlistModel = new PlaylistModel();
        $playlist = $playlistModel->find($playlistid);
    
        if (!$playlist) {
            return $this->response->setJSON(['error' => 'Playlist not found']);
        }
    
        $playlistMusicModel = new PlaylistMusicModel();
        $musicTrackIDs = $playlistMusicModel->where('playlist_id', $playlistid)->findAll();
    
        if (empty($musicTrackIDs)) {
            return $this->response->setJSON(['error' => 'No music tracks in this playlist']);
        }
    
        $musicModel = new MusicModel();
        $musicTracks = $musicModel->whereIn('id', array_column($musicTrackIDs, 'music_id'))->findAll();
    
        return $this->response->setJSON(['playlist' => $playlist, 'musicTracks' => $musicTracks]);
    }
    

    public function uploadMusic()
{
    $musicModel = new MusicModel();

    $file = $this->request->getFile('musicFile');
    $customFileName = $this->request->getPost('musicName'); // Get the custom file name

    if ($file->isValid() && $file->getClientExtension() === 'mp3') {
        $newName = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads', $newName);

        // Save the custom file name and file path to the database
        $musicModel->insert([
            'file_name' => $customFileName, // Save the custom file name
            'file_path' => 'uploads/' . $newName,
        ]);

        return redirect()->to('/')->with('success', 'Music uploaded successfully');
    } else {
        return redirect()->to('/music')->with('error', 'Invalid or unsupported file format');
    }
}

    public function addToPlaylist()
    {
        $musicID = $this->request->getPost('musicID');
        $playlistid = $this->request->getPost('playlistid');
    
        
    
        $playlistMusicModel = new PlaylistMusicModel();
        $existingAssociation = $playlistMusicModel->where('playlist_id', $playlistid)
                                                ->where('music_id', $musicID)
                                                ->countAllResults();
        
        if ($existingAssociation === 0) {
            $playlistMusicModel->insert([
                'playlist_id' => $playlistid,
                'music_id' => $musicID,
            ]);
    
            return redirect()->to('/')->with('success', 'Music added to the playlist.');
        } else {
            return redirect()->to('/')->with('error', 'Music is already in the playlist.');
        }
        return redirect()->to('/')->with('success', 'Music added to the playlist.');
    }


    public function getPlaylistMusic()
{
    $playlistid = $this->request->getPost('playlistid');
    $musicModel = new MusicModel();
    $musicList = $musicModel->where('playlist_id', $playlistid)->findAll();

    return $this->response->setJSON($musicList);
}
public function playlists($playlistid)
{
    // Load the necessary models (PlaylistModel and MusicModel) and make the necessary database queries to fetch playlist details and associated music tracks.

    $playlistModel = new PlaylistModel();
    $musicModel = new MusicModel();

    // Find the playlist by its ID
    $playlist = $playlistModel->find($playlistid);

    if (!$playlist) {
        return redirect()->to('/');
    }

    // Find the music_ids associated with the playlist
    $playlistMusicModel = new PlaylistMusicModel();
    $musicTrackIDs = $playlistMusicModel->where('playlist_id', $playlistid)->findAll();

    // Initialize an empty array to store music items
    $music = [];

    // Loop through each music_id and find the associated music track
    foreach ($musicTrackIDs as $musicTrackID) {
        $musicTrack = $musicModel->find($musicTrackID['music_id']);

        if ($musicTrack) {
            // Add the musics item to the "music" array
            $music[] = $musicTrack;
        }
    }

    // Prepare the data to be passed to the view
    $data = [
        'playlist' => $playlist,
        'musicTracks' => $music,
    ];

    // Render a view named 'player' and pass the data to it
    return view('player', $data);
}


}
