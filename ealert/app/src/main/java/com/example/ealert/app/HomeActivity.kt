package com.example.ealert.app

import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.MenuItem
import android.view.View
import android.widget.ImageButton
import android.widget.ImageView
import android.widget.TextView
import android.widget.Toast
import androidx.core.view.GravityCompat
import androidx.drawerlayout.widget.DrawerLayout
import androidx.viewpager2.widget.ViewPager2
import com.example.ealert.R
import com.example.ealert.app.ReportHistoryActivity
import com.example.ealert.app.adapter.SlideshowAdapter
import com.example.ealert.app.response.ApiResponse
import com.example.ealert.app.retrofit.RetrofitClient
import com.example.ealert.databinding.ActivityHomeBinding
import com.google.android.material.navigation.NavigationView
import com.squareup.picasso.Picasso
import okhttp3.ResponseBody
import org.json.JSONObject
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class HomeActivity : AppCompatActivity(), NavigationView.OnNavigationItemSelectedListener {
    private lateinit var binding: ActivityHomeBinding
    private lateinit var drawerLayout: DrawerLayout
    private lateinit var navigationView: NavigationView
    private lateinit var emergencyNavView: NavigationView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityHomeBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Initialize views
        drawerLayout = binding.drawerLayout
        navigationView = binding.navView
        emergencyNavView = binding.emergencyNavView
        navigationView.setNavigationItemSelectedListener(this)

        // Load user data for navigation header
        loadUserData()

        // Add back stack listener
        supportFragmentManager.addOnBackStackChangedListener {
            if (supportFragmentManager.backStackEntryCount == 0) {
                // No fragments in back stack, show home content
                findViewById<View>(R.id.home_content).visibility = View.VISIBLE
            }
        }

        // Setup slideshow
        val slideshowViewPager = findViewById<ViewPager2>(R.id.slideshowViewPager)
        val imageList = listOf(
            R.drawable.img0,
            R.drawable.img2,
            R.drawable.img4,
            R.drawable.img6,
            R.drawable.img7,
            R.drawable.img8,
            R.drawable.img9
        )

        val adapter = SlideshowAdapter(imageList)
        slideshowViewPager.adapter = adapter

        // Auto slide functionality
        val handler = Handler(Looper.getMainLooper())
        val runnable = object : Runnable {
            override fun run() {
                val currentItem = slideshowViewPager.currentItem
                slideshowViewPager.currentItem = if (currentItem == imageList.size - 1) 0 else currentItem + 1
                handler.postDelayed(this, 3000)
            }
        }
        handler.postDelayed(runnable, 3000)

        // Setup menu button
        val menuButton = findViewById<ImageButton>(R.id.menuButton)
        menuButton.setOnClickListener {
            drawerLayout.openDrawer(GravityCompat.START)
        }

        // Setup notification button
        val notificationsBtn = findViewById<ImageButton>(R.id.notificationsBtn)
        notificationsBtn.setOnClickListener {
            startActivity(Intent(this, NotificationsActivity::class.java))
        }

        // Set up emergency button
        binding.emergencyButton.setOnClickListener {
            drawerLayout.openDrawer(GravityCompat.END)
        }

        // Set up emergency navigation listener
        emergencyNavView.setNavigationItemSelectedListener { menuItem ->
            when (menuItem.itemId) {
                R.id.nav_fire -> launchEmergencyReport("FIRE", R.drawable.nav_fire)
                R.id.nav_medic -> launchEmergencyReport("MEDIC", R.drawable.nav_medic)
                R.id.nav_accident -> launchEmergencyReport("ACCIDENT", R.drawable.nav_accident)
                R.id.nav_personal -> launchEmergencyReport("PERSONAL THREAT", R.drawable.nav_personal)
                R.id.nav_others -> launchEmergencyReport("OTHERS", R.drawable.nav_others)
            }
            drawerLayout.closeDrawer(GravityCompat.END)
            true
        }
    }

    private fun loadUserData() {
        val sharedPreferences = getSharedPreferences("user_prefs", MODE_PRIVATE)
        val userId = sharedPreferences.getInt("user_id", -1)

        if (userId != -1) {
            RetrofitClient.instance.getUserProfile(userId)
                .enqueue(object : Callback<ApiResponse> {
                    override fun onResponse(call: Call<ApiResponse>, response: Response<ApiResponse>) {
                        if (response.isSuccessful) {
                            val userData = response.body()
                            if (userData != null) {
                                // Get navigation header view
                                val headerView = navigationView.getHeaderView(0)

                                // Update user image
                                val userImageView = headerView.findViewById<ImageView>(R.id.ivUserImage)
                                if (!userData.id_image.isNullOrEmpty()) {
                                    Picasso.get()
                                        .load("${RetrofitClient.BASE_URL}${userData.id_image}")
                                        .placeholder(R.drawable.ic_user_placeholder)
                                        .error(R.drawable.ic_user_placeholder)
                                        .into(userImageView)
                                }

                                // Update username
                                val userNameView = headerView.findViewById<TextView>(R.id.tvUserName)
                                userNameView.text = "${userData.firstname} ${userData.lastname}"

                                // Update email
                                val emailView = headerView.findViewById<TextView>(R.id.tvEmail)
                                emailView.text = userData.email
                            }
                        }
                    }

                    override fun onFailure(call: Call<ApiResponse>, t: Throwable) {
                        Toast.makeText(this@HomeActivity, "Failed to load user data", Toast.LENGTH_SHORT).show()
                    }
                })
        }
    }

    private fun launchEmergencyReport(emergencyType: String, emergencyTypeImage: Int) {
        val intent = Intent(this, EmergencyReportActivity::class.java).apply {
            putExtra("emergencyType", emergencyType)
            putExtra("emergencyTypeImage", emergencyTypeImage)
        }
        startActivity(intent)
    }

    override fun onNavigationItemSelected(item: MenuItem): Boolean {
            when (item.itemId) {
                R.id.nav_home -> {
                    // Show home content and remove any fragments
                    findViewById<View>(R.id.home_content).visibility = View.VISIBLE
                    supportFragmentManager.fragments.forEach { fragment ->
                        supportFragmentManager.beginTransaction().remove(fragment).commit()
                    }
                drawerLayout.closeDrawer(GravityCompat.START)
                return true
                }
                R.id.nav_rep_his -> {
                    try {
                        // Launch ReportHistoryActivity instead of fragment
                        val intent = Intent(this, ReportHistoryActivity::class.java)
                        startActivity(intent)
                        drawerLayout.closeDrawer(GravityCompat.START)
                        return true
                    } catch (e: Exception) {
                        e.printStackTrace()
                        Toast.makeText(this, "Error loading report history", Toast.LENGTH_SHORT).show()
                        return false
                    }
                }
                R.id.nav_profile -> {
                    // Hide home content and show profile fragment
                    findViewById<View>(R.id.home_content).visibility = View.GONE
                    supportFragmentManager.beginTransaction()
                    .replace(R.id.fragment_container, ProfileFragment())
                        .addToBackStack(null)
                    .commit()
                drawerLayout.closeDrawer(GravityCompat.START)
                return true
                }
                R.id.nav_logout -> {
                    val sharedPreferences = getSharedPreferences("user_prefs", MODE_PRIVATE)
                    val userId = sharedPreferences.getInt("user_id", -1)

                    if (userId != -1) {
                        RetrofitClient.instance.logoutUser(userId)
                            .enqueue(object : Callback<ResponseBody> {
                                override fun onResponse(call: Call<ResponseBody>, response: Response<ResponseBody>) {
                                    if (response.isSuccessful) {
                                        val apiResponse = response.body()?.string()
                                        if (apiResponse != null) {
                                            // Handle logout responsefg
                                            val jsonObject = JSONObject(apiResponse)
                                            if (jsonObject.getString("status") == "success") {
                                                // Clear shared preferences
                                                sharedPreferences.edit().clear().apply()

                                                // Redirect to login
                                                val intent = Intent(this@HomeActivity, LoginActivity::class.java)
                                                intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                                                startActivity(intent)
                                                finish()
                                            }
                                        }
                                    }
                                }

                                override fun onFailure(call: Call<ResponseBody>, t: Throwable) {
                                    Toast.makeText(this@HomeActivity, "Logout failed: ${t.message}", Toast.LENGTH_SHORT).show()
                                }
                            })
                    }
                }
                R.id.nav_fire -> {
                    val emergencyType = "FIRE"
                    val emergencyTypeImage = R.drawable.nav_fire
                    val intent = Intent(this, EmergencyReportActivity::class.java)
                    intent.putExtra("emergencyType", emergencyType)
                    intent.putExtra("emergencyTypeImage", emergencyTypeImage)
                    startActivity(intent)
                }
                R.id.nav_medic -> {
                    val emergencyType = "MEDIC"
                    val emergencyTypeImage = R.drawable.nav_medic
                    val intent = Intent(this, EmergencyReportActivity::class.java)
                    intent.putExtra("emergencyType", emergencyType)
                    intent.putExtra("emergencyTypeImage", emergencyTypeImage)
                    startActivity(intent)
                }
                R.id.nav_accident -> {
                    val emergencyType = "ACCIDENT"
                    val emergencyTypeImage = R.drawable.nav_accident
                    val intent = Intent(this, EmergencyReportActivity::class.java)
                    intent.putExtra("emergencyType", emergencyType)
                    intent.putExtra("emergencyTypeImage", emergencyTypeImage)
                    startActivity(intent)
                }
                R.id.nav_personal -> {
                    val emergencyType = "PERSONAL THREAT"
                    val emergencyTypeImage = R.drawable.nav_personal
                    val intent = Intent(this, EmergencyReportActivity::class.java)
                    intent.putExtra("emergencyType", emergencyType)
                    intent.putExtra("emergencyTypeImage", emergencyTypeImage)
                    startActivity(intent)
                }
                R.id.nav_others -> {
                    val emergencyType = "OTHERS(specific)"
                    val emergencyTypeImage = R.drawable.nav_others
                    val intent = Intent(this, EmergencyReportActivity::class.java)
                    intent.putExtra("emergencyType", emergencyType)
                    intent.putExtra("emergencyTypeImage", emergencyTypeImage)
                    startActivity(intent)
                }
            }
        drawerLayout.closeDrawer(GravityCompat.START)
        return true
    }

    override fun onBackPressed() {
        if (drawerLayout.isDrawerOpen(GravityCompat.START)) {
            drawerLayout.closeDrawer(GravityCompat.START)
        } else {
            super.onBackPressed()
        }
    }
}