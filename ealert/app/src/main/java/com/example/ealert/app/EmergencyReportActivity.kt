package com.example.ealert.app

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.net.Uri
import android.os.Bundle
import android.provider.MediaStore
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import androidx.lifecycle.lifecycleScope
import com.example.ealert.R
import com.example.ealert.app.retrofit.RetrofitClient
import com.example.ealert.databinding.ActivityEmergencyReportBinding
import com.google.android.gms.common.api.Status
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.OnMapReadyCallback
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.google.android.gms.maps.model.MarkerOptions
import com.google.android.libraries.places.api.Places
import com.google.android.libraries.places.api.model.Place
import com.google.android.libraries.places.widget.AutocompleteSupportFragment
import com.google.android.libraries.places.widget.listener.PlaceSelectionListener
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import okhttp3.MediaType
import okhttp3.MultipartBody
import okhttp3.RequestBody
import java.io.File

class EmergencyReportActivity : AppCompatActivity(), OnMapReadyCallback {

    private lateinit var binding: ActivityEmergencyReportBinding
    private lateinit var mMap: GoogleMap
    private var selectedImageUri: Uri? = null
    private var selectedLocation: LatLng? = null
    private var selectedAddress: String? = null
    private val REQUEST_IMAGE_PICK = 100
    private val LOCATION_PERMISSION_REQUEST = 101

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityEmergencyReportBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Initialize Places API
        if (!Places.isInitialized()) {
            Places.initialize(applicationContext, getString(R.string.google_maps_key))
        }

        // Set emergency type from intent
        val emergencyType = intent.getStringExtra("emergencyType") ?: "Emergency"
        val emergencyTypeImage = intent.getIntExtra("emergencyTypeImage", R.drawable.nav_fire)

        binding.ivFire.setBackgroundResource(emergencyTypeImage)
        binding.tvFire.text = emergencyType

        // Initialize map
        val mapFragment = supportFragmentManager
            .findFragmentById(R.id.map) as SupportMapFragment
        mapFragment.getMapAsync(this)

        // Initialize Places Autocomplete
        setupPlacesAutocomplete()

        // Request location permissions
        checkLocationPermissions()

        setupClickListeners()
    }

    private fun setupClickListeners() {
        // Back button
        binding.btnBack.setOnClickListener {
            finish()
        }

        // Emergency Call button
        binding.buttonCall.setOnClickListener {
            val intent = Intent(Intent.ACTION_DIAL).apply {
                data = Uri.parse("tel:911")
            }
            startActivity(intent)
        }

        // Submit Report button
        binding.buttonSubmitReport.setOnClickListener {
            val concernText = binding.editTextConcern.text.toString()

            if (concernText.isEmpty() || selectedLocation == null) {
                Toast.makeText(this, "Please fill all fields and select a location!", Toast.LENGTH_SHORT).show()
            } else {
                submitReport(concernText, selectedAddress ?: "Unknown location")
            }
        }

        // Attach Photo button
        binding.buttonAttachPhoto.setOnClickListener {
            val intent = Intent(Intent.ACTION_PICK, MediaStore.Images.Media.EXTERNAL_CONTENT_URI)
            startActivityForResult(intent, REQUEST_IMAGE_PICK)
        }
    }

    private fun checkLocationPermissions() {
        if (ContextCompat.checkSelfPermission(
                this,
                Manifest.permission.ACCESS_FINE_LOCATION
            ) != PackageManager.PERMISSION_GRANTED
        ) {
            ActivityCompat.requestPermissions(
                this,
                arrayOf(
                    Manifest.permission.ACCESS_FINE_LOCATION,
                    Manifest.permission.ACCESS_COARSE_LOCATION
                ),
                LOCATION_PERMISSION_REQUEST
            )
        } else {
            enableMyLocation()
        }
    }

    private fun enableMyLocation() {
        try {
            if (::mMap.isInitialized) {
                mMap.isMyLocationEnabled = true
            }
        } catch (e: SecurityException) {
            Toast.makeText(this, "Location permission required", Toast.LENGTH_SHORT).show()
        }
    }

    override fun onRequestPermissionsResult(
        requestCode: Int,
        permissions: Array<String>,
        grantResults: IntArray
    ) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        if (requestCode == LOCATION_PERMISSION_REQUEST) {
            if (grantResults.isNotEmpty() && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                enableMyLocation()
            }
        }
    }

    private fun setupPlacesAutocomplete() {
        val autocompleteFragment = supportFragmentManager
            .findFragmentById(R.id.autocomplete_fragment) as AutocompleteSupportFragment

        autocompleteFragment.setPlaceFields(listOf(
            Place.Field.ID,
            Place.Field.NAME,
            Place.Field.LAT_LNG,
            Place.Field.ADDRESS
        ))

        autocompleteFragment.setOnPlaceSelectedListener(object : PlaceSelectionListener {
            override fun onPlaceSelected(place: Place) {
                place.latLng?.let { latLng ->
                    selectedLocation = latLng
                    selectedAddress = place.address
                    updateMapLocation(latLng, place.name ?: "Selected Location")
                }
            }

            override fun onError(status: Status) {
                Toast.makeText(applicationContext, "Error: ${status.statusMessage}", Toast.LENGTH_SHORT).show()
            }
        })
    }

    override fun onMapReady(googleMap: GoogleMap) {
        mMap = googleMap

        // Set default location (Philippines)
        val philippines = LatLng(12.8797, 121.7740)
        mMap.moveCamera(CameraUpdateFactory.newLatLngZoom(philippines, 5f))

        // Enable zoom controls and my location button
        mMap.uiSettings.apply {
            isZoomControlsEnabled = true
            isMyLocationButtonEnabled = true
            isMapToolbarEnabled = true
        }

        // Check location permissions
        checkLocationPermissions()

        // Handle map click to update marker
        mMap.setOnMapClickListener { latLng ->
            selectedLocation = latLng
            updateMapLocation(latLng, "Selected Location")
        }
    }

    private fun updateMapLocation(latLng: LatLng, title: String) {
        mMap.clear()
        mMap.addMarker(MarkerOptions().position(latLng).title(title))
        mMap.animateCamera(CameraUpdateFactory.newLatLngZoom(latLng, 15f))
    }

    private fun submitReport(concern: String, location: String) {
        if (selectedLocation == null) {
            Toast.makeText(this, "Please select a location", Toast.LENGTH_SHORT).show()
            return
        }

        // Get user_id from SharedPreferences
        val sharedPreferences = getSharedPreferences("user_prefs", MODE_PRIVATE)
        val userId = sharedPreferences.getInt("user_id", -1)

        if (userId == -1) {
            Toast.makeText(this, "Please login again", Toast.LENGTH_SHORT).show()
            // Redirect to login
            val intent = Intent(this, LoginActivity::class.java)
            intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
            startActivity(intent)
            return
        }

        lifecycleScope.launch(Dispatchers.IO) {
            try {
                val service = RetrofitClient.instance

                // Create RequestBody objects
                val userIdBody = RequestBody.create(MediaType.get("text/plain"), userId.toString())

                // Set subject report based on emergency type
                val emergencyType = intent.getStringExtra("emergencyType") ?: "Emergency"
                val subjectReport = when (emergencyType) {
                    "FIRE" -> "Fire Emergency Report"
                    "MEDIC" -> "Medical Emergency Report"
                    "ACCIDENT" -> "Accident Report"
                    "PERSONAL THREAT" -> "Personal Threat Report"
                    "OTHERS" -> "Other Emergency Report"
                    else -> "Emergency Report"
                }
                val subjectReportBody = RequestBody.create(MediaType.get("text/plain"), subjectReport)

                val emergencyTypeBody = RequestBody.create(MediaType.get("text/plain"), emergencyType)
                val locationBody = RequestBody.create(MediaType.get("text/plain"), location)
                val latitude = RequestBody.create(MediaType.get("text/plain"), selectedLocation!!.latitude.toString())
                val longitude = RequestBody.create(MediaType.get("text/plain"), selectedLocation!!.longitude.toString())
                val clientInquiry = RequestBody.create(MediaType.get("text/plain"), concern)

                // Handle photo attachment if exists
                var photoPart: MultipartBody.Part? = null
                selectedImageUri?.let { uri ->
                    val file = File(getRealPathFromUri(uri))
                    val requestFile = RequestBody.create(MediaType.get("image/*"), file)
                    photoPart = MultipartBody.Part.createFormData("photo_attachment", file.name, requestFile)
                }

                val response = service.submitReport(
                    userIdBody,
                    subjectReportBody, // Use the customized subject report
                    emergencyTypeBody,
                    locationBody,
                    latitude,
                    longitude,
                    clientInquiry,
                    photoPart
                )

                withContext(Dispatchers.Main) {
                    if (response.isSuccessful) {
                        val result = response.body()
                        if (result?.status == "success") {
                            Toast.makeText(this@EmergencyReportActivity, "Report submitted successfully", Toast.LENGTH_SHORT).show()
                            finish()
                        } else {
                            Toast.makeText(this@EmergencyReportActivity, result?.message ?: "Unknown error occurred", Toast.LENGTH_SHORT).show()
                        }
                    } else {
                        Toast.makeText(this@EmergencyReportActivity, "Failed to submit report", Toast.LENGTH_SHORT).show()
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    Toast.makeText(this@EmergencyReportActivity, "Error: ${e.message}", Toast.LENGTH_SHORT).show()
                }
            }
        }
    }

    private fun getRealPathFromUri(uri: Uri): String {
        var filePath = ""
        val projection = arrayOf(MediaStore.Images.Media.DATA)
        val cursor = contentResolver.query(uri, projection, null, null, null)
        cursor?.use {
            if (it.moveToFirst()) {
                val columnIndex = it.getColumnIndexOrThrow(MediaStore.Images.Media.DATA)
                filePath = it.getString(columnIndex)
            }
        }
        return filePath
    }

    @Deprecated("Deprecated in Java")
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == REQUEST_IMAGE_PICK && resultCode == RESULT_OK) {
            selectedImageUri = data?.data
            binding.PhotoFile.text = "Photo attached"
        }
    }
}